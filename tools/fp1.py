#!/usr/bin/env python3
"""Compute runtime configuration fingerprint FP-1 for a Cloud Run service.

FP-1 is specified in CLOUD_RUN_CANADA_STAGING_RUNTIME.md. This file is the
authoritative implementation of that specification: where the document's field
table and this file disagree, this file wins and the document is the bug.

Emits a per-field digest table and a total. Field plaintext is held in memory
and is never printed, logged, or written to disk. The total is the SHA-256 of
the printed table, so the table alone is enough to re-verify the total.

Requires Python 3 and an authenticated gcloud on PATH. Read-only: it describes
the service and one revision and changes nothing.

Usage: python tools/fp1.py SERVICE REGION
"""
import hashlib
import json
import subprocess
import sys

FIELD_DIGEST_LEN = 16


def gcloud(args):
    out = subprocess.run(
        ['gcloud'] + args + ['--format=json'],
        capture_output=True, text=True, check=True, shell=(sys.platform == 'win32'),
    ).stdout
    return json.loads(out)


def probe_repr(p):
    if not p:
        return ''
    if p.get('tcpSocket'):
        return 'tcp:%s' % p['tcpSocket'].get('port', '')
    if p.get('httpGet'):
        return 'http:%s' % p['httpGet'].get('path', '')
    return ','.join(sorted(p))


def collect(service, region):
    svc = gcloud(['run', 'services', 'describe', service, '--region', region])
    traffic = [t for t in svc['status']['traffic'] if t.get('percent')]
    if len(traffic) != 1:
        sys.exit('FP-1 requires exactly one revision holding traffic; found %d'
                 % len(traffic))
    revision, percent = traffic[0]['revisionName'], traffic[0]['percent']

    rev = gcloud(['run', 'revisions', 'describe', revision, '--region', region])
    spec, meta = rev['spec'], rev['metadata']
    ann = meta.get('annotations', {})
    c = spec['containers'][0]
    env = c.get('env', [])
    limits = c.get('resources', {}).get('limits', {})

    plain = sorted('%s=%s' % (e['name'], e.get('value', ''))
                   for e in env if 'valueFrom' not in e)
    secret = sorted('%s->%s' % (e['name'], e['valueFrom']['secretKeyRef']['name'])
                    for e in env if 'valueFrom' in e)
    mounts = sorted('%s:%s' % (m['name'], m['mountPath'])
                    for m in c.get('volumeMounts', []))
    vtypes = sorted('%s:%s' % (v['name'],
                               ','.join(sorted(k for k in v if k != 'name')))
                    for v in spec.get('volumes', []))
    sql = ann.get('run.googleapis.com/cloudsql-instances', '')

    return [
        ('service', service),
        ('region', region),
        ('revision', revision),
        ('traffic_percent', percent),
        ('image_digest', c['image'].split('@', 1)[1] if '@' in c['image'] else ''),
        ('service_account', spec.get('serviceAccountName', '')),
        ('container_concurrency', spec.get('containerConcurrency', '')),
        ('timeout_seconds', spec.get('timeoutSeconds', '')),
        ('min_scale', ann.get('autoscaling.knative.dev/minScale', '')),
        ('max_scale', ann.get('autoscaling.knative.dev/maxScale', '')),
        ('cpu_limit', limits.get('cpu', '')),
        ('memory_limit', limits.get('memory', '')),
        ('container_port', (c.get('ports') or [{}])[0].get('containerPort', '')),
        ('liveness_path', (c.get('livenessProbe') or {}).get('httpGet', {}).get('path', '')),
        ('startup_probe', probe_repr(c.get('startupProbe'))),
        ('cloudsql_instances', ','.join(sorted(x for x in sql.split(',') if x))),
        ('volume_mounts', ';'.join(mounts)),
        ('volume_types', ';'.join(vtypes)),
        ('secret_env', ';'.join(secret)),
        ('plain_env', ';'.join(plain)),
    ]


def main(service, region):
    fields = collect(service, region)
    lines = []
    for name, value in fields:
        h = hashlib.sha256(('%s\x1f%s' % (name, value)).encode('utf-8'))
        lines.append('%s=%s' % (name, h.hexdigest()[:FIELD_DIGEST_LEN]))
    table = '\n'.join(lines)
    total = hashlib.sha256(table.encode('utf-8')).hexdigest()

    print(table)
    print('FP-1=%s' % total)


if __name__ == '__main__':
    if len(sys.argv) != 3:
        sys.exit(__doc__.strip().splitlines()[-1])
    main(sys.argv[1], sys.argv[2])
