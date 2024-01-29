@extends('payment-gateway.layouts.master')

@section('content')
    <center><h1>{{ translate('Please do not refresh this page') }}...</h1></center>

    <form method="POST" action="{!! route('paystack.payment',['token'=>$data->id]) !!}" accept-charset="UTF-8"
          class="form-horizontal"
          role="form">
        @csrf
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <input type="hidden" name="email"
                       value="{{$payer->email!=null?$payer->email:'required@email.com'}}">
                <input type="hidden" name="orderID" value="{{$data->attribute_id}}">
                <input type="hidden" name="amount"
                       value="{{$data->payment_amount*100}}">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="currency"
                       value="{{$data->currency_code}}">
                <input type="hidden" name="metadata"
                       value="{{ json_encode($array = ['key_name' => 'value',]) }}">
                <input type="hidden" name="reference"
                       value="{{ $reference }}">

                <button class="btn btn-block" id="pay-button" type="submit" style="display:none"></button>
            </div>

        </div>
    </form>

    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("pay-button").click();
        });
    </script>

@endsection
