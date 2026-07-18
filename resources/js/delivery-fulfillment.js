const createOrangeFulfillment = window.OrangeFulfillment;

window.OrangeFulfillment = (timeslot) => {
    const fulfillment = createOrangeFulfillment(timeslot);
    const initializeGoogleMap = fulfillment.initializeGoogleMap.bind(fulfillment);
    const initializeOpenStreetMap = fulfillment.initializeOpenStreetMap.bind(fulfillment);
    const hasMapTarget = (position) => document.getElementById('map')
        && position
        && Number.isFinite(position.lat)
        && Number.isFinite(position.lng);

    fulfillment.initializeGoogleMap = (position) => {
        if (hasMapTarget(position)) {
            return initializeGoogleMap(position);
        }
    };

    fulfillment.initializeOpenStreetMap = (position) => {
        if (hasMapTarget(position)) {
            return initializeOpenStreetMap(position);
        }
    };

    return fulfillment;
};
