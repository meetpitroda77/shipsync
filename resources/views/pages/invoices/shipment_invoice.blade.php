


<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 12px;
            color: #2c3e50;
        }

        .invoice {
            padding: 20px;
            border: 1px solid #e3e6f0;
        }


        .header {
            background: #3b6cb7;
            color: white;
            padding: 15px;
        }

        .header table {
            width: 100%;
        }

        .title {
            font-size: 28px;
            font-weight: bold;
        }


        .address-table {
            width: 100%;
            margin-top: 20px;
        }

        .address-box {
            border: 1px solid #e3e6f0;
            padding: 12px;
            border-radius: 5px;
            height: 95px;
        }

        .label {
            font-weight: bold;
            margin-bottom: 6px;
            font-size: 14px;
        }


        .shipment {
            width: 100%;
            margin-top: 15px;
            border: 1px solid #e3e6f0;
        }

        .shipment td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }


        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }

        .main-table th {
            background: #3b6cb7;
            color: white;
            padding: 10px;
            font-size: 13px;
        }

        .main-table td {
            padding: 10px;
            border-bottom: 1px solid #e3e6f0;
        }


        .summary {
            width: 100%;
            margin-top: 20px;
        }

        .box {
            border: 1px solid #e3e6f0;
            padding: 12px;
            border-radius: 5px;
            background: #f8fbff;
        }

        .total {
            font-size: 20px;
            font-weight: bold;
            margin-top: 8px;
            color: #3b6cb7;
        }


        .notes {
            margin-top: 25px;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
            font-size: 11px;
        }
    </style>

</head>

<body>

    <div class="invoice">


        <div class="header">

            <table>

                <tr>

                    <td width="60%">

                        <div class="title">
                            INVOICE
                        </div>

                        Invoice Date :
                        {{ now()->format('d-m-Y') }}

                    </td>

                    <td width="40%" align="right">

                        <strong style="font-size:16px;">
                            ShipSync
                        </strong>

                        <br>

                        Shipping Management System

                        <br>

                        India

                    </td>

                </tr>

            </table>

        </div>



        <table class="address-table">

            <tr>

                <td width="50%">

                    <div class="address-box">

                        <div class="label">
                            Billed To
                        </div>

                        {{ $shipment->sender_name }}

                        <br>

                        {{ $shipment->sender_phone }}

                        <br>

                        {{ $shipment->senderAddress->address ?? '' }}

                        <br>

                        {{ $shipment->senderAddress->city ?? '' }}

                        {{ $shipment->senderAddress->state ?? '' }}

                        {{ $shipment->senderAddress->zip_code ?? '' }}

                    </div>

                </td>


                <td width="50%">

                    <div class="address-box">

                        <div class="label">
                            Ship To
                        </div>

                        {{ $shipment->receiver_name }}

                        <br>

                        {{ $shipment->receiver_phone }}

                        <br>

                        {{ $shipment->receiverAddress->address ?? '' }}

                        <br>

                        {{ $shipment->receiverAddress->city ?? '' }}

                        {{ $shipment->receiverAddress->state ?? '' }}

                        {{ $shipment->receiverAddress->zip_code ?? '' }}

                    </div>

                </td>

            </tr>

        </table>



        <table class="shipment">

            <tr>

                <td>
                    <strong>Tracking ID</strong>
                </td>

                <td>
                    {{ $shipment->tracking_id }}
                </td>

                <td>
                    <strong>Status</strong>
                </td>

                <td>
                    {{ ucfirst($shipment->status) }}
                </td>

            </tr>

            <tr>

                <td>
                    <strong>Shipment Date</strong>
                </td>

                <td>
                    {{ now()->format('d-m-Y') }}
                </td>

                <td>
                    <strong>Delivery Date</strong>
                </td>

                <td>

                    {{ $shipment->actual_delivery_date
    ? $shipment->actual_delivery_date->format('d-m-Y')
    : 'Pending'
}}

                </td>

            </tr>

            <tr>

                <td>
                    <strong>Shipping Mode</strong>
                </td>

                <td>
                    {{ $shipment->shipping_mode }}
                </td>

                <td>
                    <strong>Courier</strong>
                </td>

                <td>
                    {{ $shipment->courier_company ?? 'N/A' }}
                </td>

            </tr>

        </table>



        <table class="main-table">

            <tr>

                <th>
                    Tracking ID
                </th>

                <th>
                    Package Details
                </th>

                <th>
                    Qty
                </th>

            </tr>

            @foreach($shipment->packages as $package)

                <tr>

                    <td>

                        {{ $shipment->tracking_id }}

                    </td>

                    <td>

                        {{ $package->description }}

                        <br>

                        Weight :
                        {{ $package->weight }} kg

                        <br>

                        Dimensions :

                        {{ $package->length }}
                        ×
                        {{ $package->width }}
                        ×
                        {{ $package->height }}

                    </td>

                    <td>

                        {{ $package->amount }}

                    </td>

                </tr>

            @endforeach

        </table>



        <table class="summary" >

            <tr>

                <td width="50%">

                    <div class="box">

                        <table width="100%">



                            <tr>

                                <td>
                                    Sub Total
                                </td>

                                <td align="right">

                                    $ {{ number_format($subtotal, 2) }}

                                </td>

                            </tr>
                            <tr>

                                <td>
                                    Tax ({{ $taxPercent * 100 }}%)
                                </td>

                                <td align="right">

                                    $ {{ number_format($taxAmount, 2) }}

                                </td>

                            </tr>

                            <tr>

                                <td>
                                    Insurance
                                </td>

                                <td align="right">

                                    $ {{ number_format($insurance, 2) }}

                                </td>

                            </tr>

                            <tr>
                                <td colspan="2">
                                    <hr>
                                </td>
                            </tr>

                            <tr>

                                <td>

                                    <strong>TOTAL DUE</strong>

                                </td>

                                <td align="right">

                                    <strong style="font-size:18px;color:#3b6cb7;">

                                        $ {{ number_format($totalAmount, 2) }}

                                    </strong>

                                </td>

                            </tr>

                        </table>

                    </div>

                </td>


                <td width="50%">

                    <div class="box">

                        <strong>
                            Payment Summary
                        </strong>

                        <br><br>

                        Payment Method :
                        {{ $payment->payment_method ?? 'N/A' }}

                        <br>

                        Transaction :
                        {{ $payment->transaction_id ?? 'N/A' }}

                        <br>

                        Payment Date :

                        {{ $payment->paid_at
    ? $payment->paid_at->format('d-m-Y')
    : 'N/A'
}}

                        <br>

                        Amount Paid :

                        $ {{ number_format($payment->amount ?? 0, 2) }}

                        <br>

                        Status :

                        {{ ucfirst($payment->payment_status ?? 'Pending') }}

                    </div>

                </td>

            </tr>

        </table>



        <div class="notes">

            <strong>
                Notes
            </strong>

            <br>

            Thank you for choosing ShipSync.


        </div>


    </div>

</body>

</html>