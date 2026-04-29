import.meta.glob('../img/**');
import './bootstrap';
import { Dropdown, Toast, Modal } from 'bootstrap';

import $ from 'jquery';

window.$ = $;
window.jQuery = $;


import { OverlayScrollbars } from "overlayscrollbars";
window.OverlayScrollbars = OverlayScrollbars;

import ApexCharts from "apexcharts";
window.ApexCharts = ApexCharts;


import Sortable from "sortablejs";
window.Sortable = Sortable;

import '../src/ts/adminlte.ts';

document.addEventListener('DOMContentLoaded', function () {
    const successToast = document.querySelector('.toast.text-bg-success');
    const errorToast = document.querySelector('.toast.text-bg-danger');

    if (successToast) {
        const toast = new Toast(successToast);
        toast.show();
    }

    if (errorToast) {
        const toast = new Toast(errorToast);
        toast.show();
    }
});


$(document).on('submit', '#createUserForm', function (e) {

    e.preventDefault();

    let form = $(this);

    $('.form-control, .form-select').removeClass('is-invalid');
    $('.invalid-feedback').text('');

    $.ajax({
        url: form.attr('action'),
        method: "POST",
        data: form.serialize(),

        success: function (response) {
            if (response.success) {
                window.location.href = response.redirect;
            }
        },

        error: function (xhr) {
            if (xhr.status == 422) {
                let errors = xhr.responseJSON.errors;

                $.each(errors, function (key, value) {
                    $('[name=' + key + ']').addClass('is-invalid');
                    $('.' + key + '_error').text(value[0]);
                });
            }
        }
    });

});





$(document).on('submit', '.updateStatusForm', function (e) {

    e.preventDefault();

    let form = $(this);
    let shipmentId = form.data('id');

    let formData = new FormData(this);

    $.ajax({
        url: `/api/${role}/shipment/${shipmentId}/UpdateShipmentStatus`,
        type: "PATCH",
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            "Accept": "application/json",
            "Authorization": "Bearer " + token
        },


        success: function (response) {

            if (response.success) {

                sessionStorage.setItem('success', response.message);
                sessionStorage.setItem('shipment_id', response.shipment_id);

                window.location.href = response.redirect;
            }
        },

        error: function (xhr) {

            let errors = xhr.responseJSON.errors;

            form.find('.invalid-feedback').remove();
            form.find('.is-invalid').removeClass('is-invalid');

            $.each(errors, function (key, value) {

                let input = form.find('[name="' + key + '"]');

                input.addClass('is-invalid');

                input.after('<div class="invalid-feedback">' + value[0] + '</div>');
            });
        }

    });

});




$(document).ready(function () {

    let msg = sessionStorage.getItem('success');
    let shipmentId = sessionStorage.getItem('shipment_id');

    let toastEl = document.getElementById('statusupdate');

    if (toastEl) {
        toastEl.classList.remove('show');
    }

    if (msg && toastEl) {

        toastEl.querySelector('.toast-body').innerText = msg;

        let toast = new Toast(toastEl);
        toast.show();

        sessionStorage.removeItem('success');
    }

    if (shipmentId) {

        let row = document.querySelector(`tr[data-id="${shipmentId}"]`);

        if (row) {

            row.style.backgroundColor = '#d1e7dd';

            row.scrollIntoView({ behavior: 'smooth', block: 'center' });

            setTimeout(() => {
                row.style.backgroundColor = '';
            }, 3000);
        }

        sessionStorage.removeItem('shipment_id');
    }

});



$(document).ready(function () {
    var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfTokenMeta) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfTokenMeta.getAttribute('content')
            }
        });
    }
});

$(document).on('change', '.role-select', function () {

    let role = $(this).val();
    let userId = $(this).data('id');

    $.ajax({
        url: "/admin/users/updateRole",
        type: "PATCH",
        data: {
            user_id: userId,
            role: role
        },

        success: function (response) {
            if (response.success) {
                window.location.href = response.redirect;
            }
        },

        error: function (xhr) {
            console.log(xhr.responseText);
        }
    });
});







let userInfo = document.getElementById('user-info');

let userRole = null;
let uId = null;

if (userInfo) {
    userRole = userInfo.getAttribute('data-role');
    uId = userInfo.getAttribute('data-user-id');
}
let badge = document.getElementById('notification-count');
let header = document.getElementById('notification-header');

let notificationCount = badge ? parseInt(badge.innerText) : 0;

function updateNotificationCount() {
    let badge = document.getElementById('notification-count');
    let header = document.getElementById('notification-header');

    if (badge) {
        badge.innerText = notificationCount;

        if (notificationCount <= 0) {
            badge.style.display = 'none';
        } else {
            badge.style.display = 'inline-block';
        }
    }

    if (header) {
        header.innerText = notificationCount + " Notifications";
    }
}

function fetchUnreadCount() {
    fetch('/notifications/unread-count', {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin'
    })
        .then(res => res.json())
        .then(data => {

            notificationCount = Number(data.count) || 0;

            updateNotificationCount();
        })
        .catch(err => {
            console.error("Fetch error:", err);
            notificationCount = 0;
            updateNotificationCount();
        });
}

function addNotificationToList(notification) {
    let notificationList = document.getElementById('notification-list');

    let emptyBox = document.getElementById('no-notification');
    if (emptyBox) emptyBox.remove();

    let item = document.createElement('a');
    item.classList.add('dropdown-item');
    item.href = "#";

    item.innerHTML = `
    <span class="notification-message">${notification.message}</span>
`;
    item.addEventListener('click', function () {
        markAsReadAndRedirect(notification.id, notification.tracking_id);
    });

    let divider = notificationList.querySelector('.dropdown-divider');
    divider.after(item);

    notificationCount++;
    updateNotificationCount();
}
window.markAsReadAndRedirect = function (notificationId, trackingId) {
    if (userRole && uId) {
        $.ajax({
            url: '/' + userRole + '/notifications/' + notificationId + '/read',
            method: 'POST',
            data: {
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            success: function () {
                fetchUnreadCount();
                window.location.href = "/" + userRole + "/shipments?search=" + trackingId;
            },
            error: function (error) {
                console.error(error);
            }
        });
    }
};

if (uId && window.Echo) {
    window.Echo.private('user.' + uId)
        .notification((notification) => {
            console.log("Notification received:", notification);

            addNotificationToList(notification);

            fetchUnreadCount();
        });


}






if (uId && window.Echo) {
    window.Echo.private('App.Models.User.' + uId)
        .notification((notification) => {
            console.log("Notification received:", notification);

            addNotificationToList(notification);
            fetchUnreadCount();
        });
}




window.showPreviewModal = function (data) {

    document.getElementById('p_sender_name').innerText = data.sender_name;
    document.getElementById('p_sender_phone').innerText = data.sender_phone;
    document.getElementById('p_sender_address').innerText = data.sender_address;

    document.getElementById('p_receiver_name').innerText = data.receiver_name;
    document.getElementById('p_receiver_phone').innerText = data.receiver_phone;
    document.getElementById('p_receiver_address').innerText = data.receiver_address;

    const tbody = document.getElementById('p_packages');
    tbody.innerHTML = '';

    let totalWeight = 0;

    data.packages.forEach((pkg, i) => {
        totalWeight += parseFloat(pkg.weight);

        tbody.innerHTML += `
        <tr>
            <td>${i + 1}</td>
            <td>${pkg.qty}</td>
            <td>${pkg.weight}</td>
            <td>${pkg.dimensions}</td>
        </tr>`;
    });

    document.getElementById('p_package_count').innerText = data.packages.length;
    document.getElementById('p_total_weight').innerText = totalWeight.toFixed(2) + " kg";

    document.getElementById('p_subtotal').innerText = data.subtotal;
    document.getElementById('p_tax').innerText = data.tax;
    document.getElementById('p_total').innerText = data.total;

    document.getElementById('p_delivery').innerText = data.delivery_method;
    document.getElementById('p_delivery_date').innerText = data.delivery_date;

    new Modal(document.getElementById('previewModal')).show();
}



$(document).on('submit', '#settingForm', function (e) {

    e.preventDefault();

    let form = $(this);

    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').text('');

    $.ajax({
        url: form.attr('action'),
        method: "POST",
        data: form.serialize(),

        success: function (response) {
            if (response.success) {

                window.location.href = response.redirect;

            }
        },

        error: function (xhr) {
            if (xhr.status == 422) {
                let errors = xhr.responseJSON.errors;

                $.each(errors, function (key, value) {
                    $('[name=' + key + ']').addClass('is-invalid');
                    $('.' + key + '_error').text(value[0]);
                });
            }
        }
    });

});



$(document).on('submit', '.updateSettingForm', function (e) {

    e.preventDefault();

    let form = $(this);

    form.find('.form-control').removeClass('is-invalid');
    form.find('.invalid-feedback').text('');

    $.ajax({
        url: form.attr('action'),
        method: "POST",
        data: form.serialize(),

        success: function (response) {
            if (response.success) {

                window.location.href = response.redirect;
            }
        },

        error: function (xhr) {
            if (xhr.status == 422) {
                let errors = xhr.responseJSON.errors;

                $.each(errors, function (key, value) {
                    form.find('[name=' + key + ']').addClass('is-invalid');
                    form.find('.' + key + '_error').text(value[0]);
                });
            }
        }
    });

});