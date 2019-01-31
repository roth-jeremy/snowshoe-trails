<html>
    <head>
        <title>Sentiers raquettes</title>
        <script type="text/javascript" src="js/config.js"></script>
    </head>

        <script type="text/javascript">
            var map;

            var tracks = <?php include('./getTracks.php'); ?>;
            var summits = <?php include('./getSummits.php'); ?>;
            var format = new ol.format.GeoJSON();

            var trackFeatures = format.readFeatures(tracks, {featureProjection: 'EPSG:3857'});
            var summitFeatures = format.readFeatures(summits, {featureProjection: 'EPSG:3857'});

            console.log(trackFeatures);
            console.log(summitFeatures);
            
            $(document).ready(function(){
                map = new ol.Map({
                target: 'map',
                layers: [
                    new ol.layer.Tile({
                        source: new ol.source.BingMaps({
                            key: 'AqE05oJsq-bWa50FPOW2S0eQm9Oqqygc1VTi_WPhUIoKR_-jgA559CRbfndgWAIz',
                            imagerySet: 'AerialWithLabels'
                        })
                    }),
                ]}
                );

                trackLayer = new ol.layer.Vector({
                        source: new ol.source.Vector({
                            features: trackFeatures,
                            format: format
                        }),
                        title: "Tracks",
                })

                summitLayer = new ol.layer.Vector({
                        source: new ol.source.Vector({
                            features: summitFeatures,
                            format: format
                        }),
                        title: "Summits",
                        style: new ol.style.Style({
                            stroke: new ol.style.Stroke({
                                color: 'blue',
                                width: 3
                            }),
                            fill: new ol.style.Fill({
                                color: 'rgba(0, 0, 255, 0.1)'
                            })
                        })
                })

                //summitLayer.setStyle(summitStyle);
                
                map.getView().setCenter(ol.proj.transform([6.15,46.2],"EPSG:4326","EPSG:3857"));
                map.getView().setZoom(10);

                map.addLayer(trackLayer);
                map.addLayer(summitLayer);
            });

            </script>
    <body>
        

        <div id="map"></div>
    </body>
</html>