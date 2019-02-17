function sendRequest(username, nonce, created, password) {
    $.soap({

        url: 'http://www.beautyfort.com/api/wsdl/v2/wsdl.wsdl',
        method: 'GetStockFile',
        enableLogging: true,

        SOAPHeader: {
            Username: username,
            Nonce: nonce,
            Created: created,
            Password: password
        },

        data: {
            TestMode: true,
            StockFileFormat: 'JSON',
            SortBy: 'StockCode'
        },

        success: function (soapResponse) {
            // do stuff with soapResponse
            // if you want to have the response as JSON use soapResponse.toJSON();
            // or soapResponse.toString() to get XML string
            // or soapResponse.toXML() to get XML DOM

            $('.responseConsole').append(soapResponse);
        },
        error: function (SOAPResponse) {
            $('.responseConsole').append(SOAPResponse);
        }
    });
}