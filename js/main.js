var xmlhttp = new XMLHttpRequest();

$(document).ready(function() {
    openRequest();
});

function sendRequest(request) {
    xmlhttp.setRequestHeader('Content-Type', 'text/xml');
    xmlhttp.send(request);
};

function openRequest() {
    xmlhttp.open('POST', 'http://www.beautyfort.com/api/soap', true);
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4) {
            if (xmlhttp.status == 200) {
                alert('done. use firebug/console to see network response');
            }
        }
    }
};

function buildRequest() {
    var request =
        '<?xml version="1.0" encoding="utf-8"?>\
        <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:bf="http://www.beautyfort.com/api/">\
            <soap:Header>\
                <bf:AuthHeader> AuthHeader\
                <bf:Username>Joe</bf:Username>\
                <bf:Nonce> string </bf:Nonce>\
                <bf:Created> dateTime </bf:Created>\
                <bf:Password>ArbwT1ckBz2ymoEW46ZO5H8alg3uDCUGhXtjseVKxNfM</bf:Password>\
            </soap:Header>\
            <soap:Body>\
                <bf:GetStockFileRequest> GetStockFileRequestType\
                    <bf:TestMode> boolean </bf:TestMode>\
                    <bf:StockFileFormat> StockFileFormat (string) </bf:StockFileFormat>\
                    <bf:FieldDelimiter> StringLength1 (string) </bf:FieldDelimiter>\
                    <bf:StockFileFields> ArrayOfStockFileField\
                    <bf:StockFileField> StockFileField (string) </bf:StockFileField>\
                    </bf:StockFileFields>\
                    <bf:SortBy> StockFileSort (string) </bf:SortBy>\
                </bf:GetStockFileRequest>\
            </soap:Body>\
        </soap:Envelope>';
        return request;
};

$('.btn').click(function() {
    sendRequest(buildRequest());
});