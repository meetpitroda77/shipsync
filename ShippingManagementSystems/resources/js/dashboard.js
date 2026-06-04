document.addEventListener("DOMContentLoaded", function () {

    let trackingElement = document.getElementById("trackingID");

    if (!trackingElement) return;

    let trackingID = trackingElement.value;

    if (window.Echo && trackingID != "") {

        window.Echo.channel('shipment.' + trackingID)

            .listen('.ShipmentStatusUpdated', (data) => {

                console.log("Realtime:", data);

                let status = document.getElementById("statusText");

                if (status) {
                    status.innerText = data.status;
                }

                updateStepper(data.status);
                window.location.reload();


            });

    }

});

function updateStepper(status) {

    document.querySelectorAll(".step").forEach(step => {

        let stepStatus = step.getAttribute("data-status");

        step.classList.remove("completed", "active");

        let circle = step.querySelector(".step-circle");

        if (stepStatus == status) {

            step.classList.add("active");

            circle.style.background = "#0d6efd";

        }
        else {

            step.classList.add("completed");

            circle.style.background = "#28a745";

        }

    });

}




