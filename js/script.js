// This script will fetch the data needed, AKA the snowshoeing routes in Valais from the 
// CampToCamp API (https://api.camptocamp.org)


function getData() {
    fetch('https://api.camptocamp.org/routes?a=14384&act=snowshoeing&pl=fr&limit=200')
        .then(function (response) {
            return response.json();
        })
        .then(function (myJson) {

            const result = myJson.documents.filter(object => object.geometry.has_geom_detail == true);

            console.log(result);
            var ArrayID = [];
            for (var i = 0; i < result.length; i++) {
                ArrayID[i] = result[i].document_id;
            }
            console.log(ArrayID);


            var ArrayChemins = [];
            for (j = 0; j < ArrayID.length; j++) {
                console.log(ArrayID[j]);
                fetch('https://api.camptocamp.org/routes/' + ArrayID[j])
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (test) {
                    console.log(test);
                    
                    
                    });

            }
        
        });
}
