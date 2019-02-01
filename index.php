<html>
    <head>
        <title>Sentiers raquettes</title>

        <link rel="stylesheet" href="styles/style.css">
        <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet"> 
        <script type="text/javascript" src="js/config.js"></script>
        <script type="text/javascript" src="js/ol-popup.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/showdown/1.9.0/showdown.js" integrity="sha256-YpZ3n25FojLhv7mP9TGZz7SkreVaLXUV3sp0fQEQDg0=" crossorigin="anonymous"></script>
    </head>
    <body>
        <script type="text/javascript">
            var converter;
            var map;
            var popup;

            var tracks = <?php include('./getTracks.php'); ?>;
            var summits = <?php include('./getSummits.php'); ?>;
            var huts = <?php include('./getHuts.php'); ?>;
            var format = new ol.format.GeoJSON();

            var trackFeatures = format.readFeatures(tracks, {featureProjection: 'EPSG:3857'});
            var summitFeatures = format.readFeatures(summits, {featureProjection: 'EPSG:3857'});
            var hutFeatures = format.readFeatures(huts, {featureProjection: 'EPSG:3857'});

            console.log(tracks);
            // console.log(hutFeatures);
            
            $(document).ready(function(){
                // Setup the map
                map = new ol.Map({
                target: 'map',
                layers: [
                    new ol.layer.Tile({
                        source: new ol.source.BingMaps({
                            key: 'AqE05oJsq-bWa50FPOW2S0eQm9Oqqygc1VTi_WPhUIoKR_-jgA559CRbfndgWAIz',
                            imagerySet: 'AerialWithLabels'
                        })
                    }),
                    new ol.layer.Tile({
                        source: new ol.source.BingMaps({
                            key: 'AqE05oJsq-bWa50FPOW2S0eQm9Oqqygc1VTi_WPhUIoKR_-jgA559CRbfndgWAIz',
                            imagerySet: 'Aerial'
                        })
                    }),
                ]}

                
                );

                // Setup the markdown interpreter
                converter = new showdown.Converter();

                // Setup the info popup
                popup = new ol.Overlay.Popup;
                popup.setOffset([-200, -200]);
                map.addOverlay(popup);

                trackLayer = new ol.layer.Vector({
                        source: new ol.source.Vector({
                            features: trackFeatures,
                            format: format
                        }),
                        title: "Tracks",
                        style: trackStyle,
                        maxResolution: 25
                })

                summitLayer = new ol.layer.Vector({
                        source: new ol.source.Vector({
                            features: summitFeatures,
                            format: format
                        }),
                        style: summitIconStyle,
                        title: "Summits",
                        maxResolution: 25
                })

                hutLayer = new ol.layer.Vector({
                        source: new ol.source.Vector({
                            features: hutFeatures,
                            format: format
                        }),
                        style: hutStyle,
                        title: "Huts",
                        maxResolution: 25,
                })
                
                summitIconLayer = new ol.layer.Vector({
                        source: new ol.source.Vector({
                            features: summitFeatures,
                            format: format
                        }),
                        style: trackIconStyle,
                        title: "Summits",
                        minResolution: 25,
                })

                function trackIconStyle(feature) {
                    var style = new ol.style.Style({
                        image: new ol.style.Icon({
                            scale: 0.08,
                            src: './img/snowshoe-pin.png',
                            opacity: 0.8
                        })
                    });
                    return [style];
                }

                function summitIconStyle(feature) {
                    var style = new ol.style.Style({
                        image: new ol.style.Icon({
                            scale: 0.08,
                            src: './img/summit-pin.png',
                            opacity: 0.8
                        })
                    });
                    return [style];
                }

                function trackStyle(feature) {
                    var style = new ol.style.Style({
                        stroke: new ol.style.Stroke({
                            width: 3,
                            color: [0, 102, 255],
                        }),
                        fill: new ol.style.Fill({
                            opacity: 0.5,
                            color: [0, 102, 255]
                        })
                    });
                    return [style];
                }

                function hutStyle(feature) {
                    var style = new ol.style.Style({
                        image: new ol.style.Icon({
                            scale: 0.08,
                            src: './img/hut-pin.png',
                            opacity: 0.8
                        })
                    });
                    return [style];
                }

                map.getView().setCenter(ol.proj.transform([6.15,46.2],"EPSG:4326","EPSG:3857"));
                map.getView().setZoom(10);

                map.addLayer(summitIconLayer);
                map.addLayer(trackLayer);
                map.addLayer(summitLayer);
                map.addLayer(hutLayer);

                // Bind marker click
                map.on('singleclick', function(evt) {
                    var feature , track;
                    
                    feature = map.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
                        return feature;
                    } , this, function(layer) {
                        return layer === trackLayer;
                    });
                    console.log(feature);
                    // Cancel event if no feature is found
                    if(!feature) return;

                    track = tracks.features.find(t => t.id === feature.id_);
                    
                    // Show popup if user clicked on a track
                    if (track) {
                        setTrackInfo(track);
                        $('#track-info').html(html);
                    }
                    
                });

                // Initialize layer switcher
                initMyLayerSwitcher();

            });

            function setTrackInfo(track) {
                // Convert the track description from markdown
                var html = converter.makeHtml(track.description);
                $("#info-none").hide()
                // Set the informations in the panel
                $('#track-title').html("Titre : " + track.title);
                $('#track-difficulty').html("Difficulté : " + track.difficulty);
                $('#track-alt-difference').html("Dénivelé : " + track.altDiff + "m");
                $('#track-area').html("Zone : " + track.area);
                $('#track-info').html(html);
            }

             /***
             * initMyLayerSwitcher: a first attempt to manage baselayers and overlayers.
             * 
             * Remark: in the current state, the main drawback is that it is absolutly 
             * not flexible and adaptative!
             * 
             * @returns {undefined}
             */
            function initMyLayerSwitcher(){
                lyrs = map.getLayers().getArray();
                
                // Set visibility of the baselayer
                lyrs[0].setVisible(true);
                lyrs[1].setVisible(false);
                
                // Define the callback when the user does change the baselayer
                $("#base").change(function (e) {
                    // Reset all baselayers
                    lyrs[0].setVisible(false);
                    lyrs[1].setVisible(false);

                    // Activate the one that is selected (based on index)
                    var idxVisible = $(this).find(":selected").index();
                    lyrs[idxVisible].setVisible(true);
                });
            }

        </script>
        <div id="window">
        <div id="map-panel" class="panel">
            <div id="map"></div>
        </div>


        <div id="side-panel" class="panel">
            <div id="panel-item">
                <h1>Sentiers de raquettes</h1>
                <p>Projet OpenLayers 3 par Romane Dubois et Jeremy Roth</p>
                
            </div>
            <div id="switcher" class="panel-item">
                <h2>Options</h2>
                <div>
                <label>Calque</label>
                <select id="base">
                    <option value="base1">Avec labels</option>
                    <option value="base2">Sans labels</option>
                </select>
        </div>
            </div>
            <div id="info-panel" class="panel-item">
                <h2>Informations</h2>
                    <div id="info-content">
                        <h3 id="track-title"></h3>
                        <h3 id="track-area"></h3>
                        <h3 id="track-difficulty"></h3>
                        <h3 id="track-alt-difference"></h3>
                        <div id="track-info">
                    </div>
                </div>
            </div>
        </div>
        </div>
    </body>
</html>