package batchelor;

import batchelor.soap.*;

/**
 * Simple client using the SOAP proxy client.
 */
public class Client {

    SoapServiceHandlerService service = new SoapServiceHandlerService();
    SoapServiceHandlerPortType client = service.getSoapServiceHandlerPort();

    public Client() {

    }

    void version() {
        System.out.println(client.version());
    }

    void queue() {
        client.queue("started", "none").
                forEach((job) -> {
                    dump(job);
                });
    }

    void submit(String data) {
        JobData indata = new JobData();
        indata.setType("data");
        indata.setData(data);

        QueuedJob job = client.enqueue(indata);
        dump(job);
    }

    void dump(QueuedJob job) {
        JobSubmit submit = job.getSubmit();
        JobStatus status = job.getStatus();
        DateTime queued = status.getQueued();

        System.out.println("Task: " + submit.getTask() + " (" + submit.getName() + ")");
        System.out.println("    Queued: " + queued.getDate() + "[" + queued.getTimezone() + "]");
        System.out.println("    State: " + status.getState().getValue());
    }

    public static void main(String[] args) {
        Client client = new Client();
        client.version();
        client.queue();
        client.submit("hello world");
    }
}
