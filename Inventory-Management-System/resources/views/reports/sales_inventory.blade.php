@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Sales & Inventory Report</h2>

    @if($data->isEmpty())
        <div class="alert alert-info">No data available.</div>
    @else
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>#ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Input Quantity</th>
                    <th>Output Quantity</th>
                    <th>Calculated Stock</th>
                    <th>Cost Price</th>
                    <th>Sale Price</th>
                    <th>Profit</th>
                    <th>Reorder Threshold</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $product)
                    <tr>
                        <td>{{ $product['id'] }}</td>
                        <td>{{ $product['name'] }}</td>
                        <td>{{ $product['category'] }}</td>
                        <td>{{ $product['supplier'] }}</td>
                        <td>{{ $product['inputQty'] }}</td>
                        <td>{{ $product['outputQty'] }}</td>
                        <td>{{ $product['calculatedStock'] }}</td>
                        <td>{{ number_format($product['inputCost'], 2) }}</td>
                        <td>{{ number_format($product['salePrice'], 2) }}</td>
                        <td>{{ number_format($product['profit'], 2) }}</td>
                        <td>{{ $product['reorderThreshold'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
