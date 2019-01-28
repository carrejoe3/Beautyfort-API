$(document).ready(function() {
});

function sendRequest() {
    $.soap({
        enableLogging: true,
        data: xml.join('')

        // success: function (soapResponse) {
        //     // do stuff with soapResponse
        //     // if you want to have the response as JSON use soapResponse.toJSON();
        //     // or soapResponse.toString() to get XML string
        //     // or soapResponse.toXML() to get XML DOM
        // },
        // error: function (SOAPResponse) {
        //     console.log(SOAPResponse);
        // }
    });
};

$('.btn').click(function() {
    sendRequest();
    console.log(new Date().toLocaleString());
});

var xml = [
'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:api="http://www.beautyfort.com/api/">',
'<soapenv:Header>',
   '<api:AuthHeader>',
      '<api:Username>joe</api:Username>',
      '<api:Nonce>2</api:Nonce>',
      '<api:Created>2019-01-28T21:16:00.000Z</api:Created>',
      '<api:Password>ArbwT1ckBz2ymoEW46ZO5H8alg3uDCUGhXtjseVKxNfM</api:Password>',
   '</api:AuthHeader>',
'</soapenv:Header>',
'<soapenv:Body>',
   '<api:GetStockFileRequest>',
      '<api:TestMode>true</api:TestMode>',
      '<api:StockFileFormat>JSON</api:StockFileFormat>',
      '<api:FieldDelimiter>,</api:FieldDelimiter>',
      '<api:StockFileFields>',
         '<api:StockFileField>?</api:StockFileField><api:StockFileField>StockCode</api:StockFileField><api:StockFileField>Category</api:StockFileField><api:StockFileField>Brand</api:StockFileField><api:StockFileField>StockLevel</api:StockFileField>',
      '</api:StockFileFields>',
      '<api:SortBy>StockLevel</api:SortBy>',
   '</api:GetStockFileRequest>',
'</soapenv:Body>',
'</soapenv:Envelope>'];