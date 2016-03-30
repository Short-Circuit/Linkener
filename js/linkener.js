/**
 * Created by Caleb Milligan on 3/29/2016.
 */
function createLink() {
    var url = document.getElementById("input_url").value.trim();
    var name = document.getElementById("input_name").value.trim();
    if (!validateAlias(name)) {
        parseResponse(1);
        return;
    }
    url = encodeURIComponent(url);
    var request = new XMLHttpRequest();
    request.open("GET", "index.php?action=create&url=" + url + "&name=" + name, true);
    request.send();
    request.onreadystatechange = function () {
        if (request.readyState == 4) {
            if (request.status == 200) {
                var response = JSON.parse(request.responseText);
                if (parseResponse(response[0])) {
                    var output = document.getElementById("link_output");
                    output.setAttribute("href", response[1]);
                    output.innerHTML = response[1];
                }
            }
        }
    }
}

function parseResponse(code) {
    switch (code) {
        case 0:
            console.log("OK");
            return true;
        case 1:
            console.log("Invalid alias");
            break;
        case 2:
            console.log("Alias exists");
            break;
    }
    return false;
}

function validateAlias(alias) {
    return !alias || /^(?=.*[0-9]*)(?=.*[a-zA-Z_-])([a-zA-Z0-9_-]+)$/.test(alias);
}