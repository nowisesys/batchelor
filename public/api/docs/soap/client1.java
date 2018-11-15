package batchelor;

import batchelor.soap.*;

class Client {

    public static void main(String[] args) {
        SoapServiceHandlerService service = new SoapServiceHandlerService();
        SoapServiceHandlerPortType client = service.getSoapServiceHandlerPort();

        System.out.println(client.version());
    }
}
