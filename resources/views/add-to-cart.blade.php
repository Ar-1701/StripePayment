<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add to Cart Page</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        @import url("https://fonts.googleapis.com/css?family=Quicksand:400,700");

        *,
        ::before,
        ::after {
            box-sizing: border-box;
        }

        body {
            font-family: "Quicksand", sans-serif;
            text-align: center;
            line-height: 1.5em;
            /*   background:#E0E4CC; */
            background: #69d2e7;
            background: -moz-linear-gradient(-45deg,
                    #69d2e7 0%,
                    #a7dbd8 25%,
                    #e0e4cc 46%,
                    #e0e4cc 54%,
                    #f38630 75%,
                    #fa6900 100%);
            background: -webkit-linear-gradient(-45deg,
                    #69d2e7 0%,
                    #a7dbd8 25%,
                    #e0e4cc 46%,
                    #e0e4cc 54%,
                    #f38630 75%,
                    #fa6900 100%);
            background: linear-gradient(135deg,
                    #69d2e7 0%,
                    #a7dbd8 25%,
                    #e0e4cc 46%,
                    #e0e4cc 54%,
                    #f38630 75%,
                    #fa6900 100%);
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#69d2e7', endColorstr='#fa6900', GradientType=1);
        }

        hr {
            border: none;
            background: #e0e4cc;
            height: 1px;
            /*   width:60%;
  display:block;
  margin-left:0; */
        }

        .container {
            max-width: 800px;
            margin: 1em auto;
            background: #fff;
            padding: 30px;
            border-radius: 5px;
        }

        .productcont {
            display: flex;
        }

        .product {
            padding: 1em;
            border: 1px solid #e0e4cc;
            margin-right: 1em;
            border-radius: 5px;
        }

        .cart-container {
            border: 1px solid #e0e4cc;
            border-radius: 5px;
            margin-top: 1em;
            padding: 1em;
        }

        button,
        input[type="submit"] {
            border: 1px solid #fa6900;
            color: #fff;
            background: #f38630;
            padding: 0.6em 1em;
            font-size: 1em;
            line-height: 1;
            border-radius: 1.2em;
            transition: color 0.2s ease-in-out, background 0.2s ease-in-out,
                border-color 0.2s ease-in-out;
        }

        button:hover,
        button:focus,
        button:active,
        input[type="submit"]:hover,
        input[type="submit"]:focus,
        input[type="submit"]:active {
            background: #a7dbd8;
            border-color: #69d2e7;
            color: #000;
            cursor: pointer;
        }

        table {
            margin-bottom: 1em;
            border-collapse: collapse;
        }

        table td,
        table th {
            text-align: left;
            padding: 0.25em 1em;
            border-bottom: 1px solid #e0e4cc;
        }

        #carttotals {
            margin-right: 0;
            margin-left: auto;
        }

        .cart-buttons {
            width: auto;
            margin-right: 0;
            margin-left: auto;
            display: flex;
            justify-content: flex-end;
            padding: 1em 0;
        }

        #emptycart {
            margin-right: 1em;
        }

        table td:nth-last-child(1) {
            text-align: right;
        }

        .message {
            border-width: 1px 0px;
            border-style: solid;
            border-color: #a7dbd8;
            color: #679996;
            padding: 0.5em 0;
            margin: 1em 0;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Simple JavaScript Shopping Cart</h1>
        <div id="alerts"></div>
        <div class="productcont">
            @php
                $qtyArr = [];
                $priceArr = [];
            @endphp
            @foreach ($product as $item)
                <div class="product">
                    <h3 class="productname">{{ $item->product_name }}</h3>
                    <p>Product description excerpt...</p>
                    <p class="price">${{ $item->price }}</p>
                    <p class="qty">
                    <form>
                        <input type="number" name="" id="qty"value='{{ $item->qty }}'
                            data-id="{{ $item->id }}" class="form-control w-50" onkeyup="updateQty(this)">
                    </form>
                    </p>
                </div>
                @php
                    array_push($priceArr, $item->qty * $item->price);
                    array_push($qtyArr, $item->qty);
                @endphp
            @endforeach
            @php
                // print_r(array_sum($priceArr));
            @endphp
        </div>
        <table id="carttotals">
            <tr>
                <td><strong>Items</strong></td>
                <td><strong>Total</strong></td>
            </tr>
            <tr>
                <td>x<span id="itemsquantity">{{ array_sum($qtyArr) == '' ? 0 : array_sum($qtyArr) }}</span></td>

                <td>$<span id="total">{{ array_sum($priceArr) == '' ? 0 : array_sum($priceArr) }}</span></td>
            </tr>
        </table>
        <a href="{{ url('stripe') . '?user_id=1' }}" class="btn btn-warning mt-3">Checkout</a>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            $("#checkout").click(function() {
                var id = $("#id").val();
                var total = $("#total").text();
                var qty = $("#qty").val();
                console.log(qty);
                // Use AJAX to send a POST request with data
                // $.ajax({
                //     url: "{{ url('stripe') }}", // Adjust the URL as needed
                //     type: "post",
                //     data: {
                //         id: id,
                //         total: total,
                //         qty: qty,
                //         _token: "{{ @csrf_token() }}"
                //     },
                //     success: function(data) {
                //         // Handle the success response if needed
                //         console.log(data);
                //     },
                //     error: function(xhr, status, error) {
                //         // Handle errors if needed
                //         console.error(xhr.responseText);
                //     }
                // });
            });
        });

        function updateQty(elem) {
            var qty = $(elem).val();
            var id = $(elem).data('id');
            $.ajax({
                url: "{{ url('update_qty') }}",
                type: "post",
                data: {
                    id: id,
                    qty: qty,
                    _token: "{{ @csrf_token() }}"
                },
                success: function(data) {
                    console.log(data);
                }
            });
        }

        function addtocart() {
            var id = $('#id').val();
            var productname = $('#productname').val();
            var price = $('#price').val();
            var qty = $('#qty').val();
            console.log(qty)
            // $.ajax({
            //     url: "{{ url('save-add-tocart') }}",
            //     type: "post",
            //     data: {
            //         id: id,
            //         productname: productname,
            //         price: price,
            //         qty: qty,
            //         _token: "{{ @csrf_token() }}"
            //     },
            //     success: function(data) {
            //         console.log(data);
            //     }
            // });
        }
    </script>
</body>

</html>
