
function liveTracking(map, importLayer, data, setStyleHelper) {
    var timeout;
    var importLayer = importLayer;
    
    var layerKey = importLayer.key;
    
    importLayer.redraw();
    
    map.events.register('changelayer', null, function(evt){
       if(evt.property === "visibility") {
         if (evt.layer.key == layerKey) {
           if (evt.layer.visibility) {
             importLayer.destroyFeatures();
             liveRequest();
           } else {
             window.clearTimeout(timeout)
           }
         }
       }
    });

    var fnLiveCallback = function urlRequestHandler(request) {
        var requestData = JSON.parse(request.responseText);
        importLayer.destroyFeatures();
        if (!requestData.error)
        {
          var options = {
              internalProjection : map.getProjectionObject(),
              externalProjection : new OpenLayers.Projection('EPSG:4326')
          };
          var importFormat = new OpenLayers.Format.GeoJSON(options);
          importLayer.addFeatures(importFormat.read(requestData));//, data));
        }
        timeout = window.setTimeout(function(){liveRequest()}, 10000);
    }

    var liveRequest = function() {
        OpenLayers.Request.GET({
            url : 'system/modules/con4gis_core/api/trackingService?method=getLive',
            callback: fnLiveCallback
        });
    };

    if (importLayer.visibility) {
      liveRequest();
    }

}