<h2 style="text-align:center;">Shipping Report</h2>



<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        page-break-inside: auto;
    }

    th,
    td {
        border: 1px solid #000;
        padding: 4px;
        text-align: center;
    }

    thead {
        display: table-header-group;
    }

    tfoot {
        display: table-footer-group;
    }

    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }

    th {
        background-color: #f2f2f2;
    }
</style>
<table border="1" width="100%" cellpadding="5">
    <thead>
        <tr>
            <th>
                Tracking ID
            </th>

            <th>
                Date
            </th>
            <th>
                Sender
            </th>

            <th>
                Origin
            </th>

            <th>
                Status
            </th>
            <th>Weight</th>
            <th>Subtotal</th>
            <th>Insurance</th>
            <th>Tax</th>
            <th>Total</th>

        </tr>
    </thead>

    <tbody>
        @forelse ($report as $shipment)
            <tr>
                <td>{{ $shipment['tracking'] }}</td>
                <td>{{ $shipment['date'] }}</td>
                <td>{{ $shipment['sender'] }}</td>
                <td>{{ $shipment['origin'] }}</td>
                <td>{{ $shipment['status'] }}</td>
                <td>{{ $shipment['weight'] }}</td>
                <td>{{ $shipment['subtotal'] }}</td>
                <td>{{ $shipment['insurance'] }}</td>
                <td>{{ $shipment['tax'] }}</td>
                <td>{{ $shipment['total'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="14" style="text-align: center">No shipments found</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="5">Total</th>
            <th>{{ $totals['weight'] }}</th>
            <th>{{ $totals['subtotal']}}</th>
            <th>{{ $totals['insurance'] }}</th>
            <th>{{ $totals['tax'] }}</th>
            <th>{{ $totals['total'] }}</th>
        </tr>
    </tfoot>
</table>