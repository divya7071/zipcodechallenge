importScripts('https://unpkg.com/@turf/turf@6/turf.min.js');

self.onmessage = function (e) {
    const { route, zipData } = e.data;

    const routeBBox = turf.bbox(route);
    const routeBBoxPoly = turf.bboxPolygon(routeBBox);

    const matchedZips = [];
    const matchedFeatures = [];

    for (const zip of zipData.features) {
        if (!zip.geometry) continue;

        if (!turf.booleanIntersects(routeBBoxPoly, zip)) continue;

        if (turf.booleanIntersects(zip, route)) {
            const code = zip.properties?.postcode;
            if (code) {
                matchedZips.push(code);
                matchedFeatures.push(zip);
            }
        }
    }

    self.postMessage({
        zips: [...new Set(matchedZips)],
        features: matchedFeatures
    });
};
