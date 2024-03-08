@extends('layouts.app')
@section('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<style>
    .StripeElement {
        background-color: white;
        padding: 8px 12px;
        border-radius: 4px;
        border: 1px solid transparent;
        box-shadow: 0 1px 3px 0 #e6ebf1;
        -webkit-transition: box-shadow 150ms ease;
        transition: box-shadow 150ms ease;
    }
    .StripeElement--focus {
        box-shadow: 0 1px 3px 0 #cfd7df;
    }
    .StripeElement--invalid {
        border-color: #fa755a;
    }
    .StripeElement--webkit-autofill {
        background-color: #fefde5 !important;
    }
</style>

@endsection
@section('content')
<div class="container mt-5">
    <div class="card">
        <h5 class="card-header">Quick Pay</h5>
        <div class="card-body">
            <form action="{{route('subscribe')}}" method="post" id="subscribe-form">
                @csrf
                <input type="number" name="amount" id="amount" class="form-control"><br>
                <label for="card-holder-name form-control">Card Holder Name</label><br>
                <input type="text"  id="card-holder-name">
                <div class="form-row">
                    <label for="card-element">Credit or debit card</label>
                    <div class="form-control" id="card-element"></div>
                    <div id="card-errors" role="alert" class="text-danger"></div>
                </div>
                <div class="stripe-errors"></div>
                @if (count($errors) > 0)
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                    @endforeach
                </div>
                @endif
                <div class="form-group text-center">
                    <button  id="card-button" data-secret="{{ $intent->client_secret }}" class="btn btn-lg btn-success btn-block">SUBMIT</button>
                </div>
            </form>
        </div>
    </div>
</div>
    <!-- notification testing -->
    <section class="my-5">
        <div class="container my-5">
            <form action="{{ route('notification') }}" method="post">
                @csrf
                <div class="card">
                    @if($errors->any())
                        <div class="alert alert-danger mt-2" role="alert">
                            @foreach ($errors->all() as $error)
                            {{$error}} <br>
                            @endforeach
                        </div>
                    @endif
                    <h3 class="card-header">Notification testing</h3>
                    <div class="mb-3">
                        <label for="name" class="form-label ms-1">Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter Product Name">
                    </div>
                    <button class="btn-sm btn-primary w-25 m-1" type="submit">Submit</button>
                </div>
            </form>
        </div>
    </section>
@endsection
@section('script')
<script src="https://js.stripe.com/v3/"></script>
<script>
    var stripe = Stripe('{{ env('STRIPE_KEY') }}');
    var elements = stripe.elements();
    var style = {
        base: {
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    };
    var card = elements.create('card', {hidePostalCode: true,
        style: style});
    card.mount('#card-element');
    card.addEventListener('change', function(event) {
        var displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });
    const cardHolderName = document.getElementById('card-holder-name');
    const cardButton = document.getElementById('card-button');
    const clientSecret = cardButton.dataset.secret;
    cardButton.addEventListener('click', async (e) => {
        e.preventDefault();
        console.log("attempting");
        const { setupIntent, error } = await stripe.confirmCardSetup(
            clientSecret, {
                payment_method: {
                    card: card,
                    billing_details: { name: cardHolderName.value }
                }
            }
            );
        if (error) {
            var errorElement = document.getElementById('card-errors');
            errorElement.textContent = error.message;
        } else {
            paymentMethodHandler(setupIntent.payment_method);
        }
    });
    function paymentMethodHandler(payment_method) {
        var form = document.getElementById('subscribe-form');
        var hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', 'payment_method');
        hiddenInput.setAttribute('value', payment_method);
        form.appendChild(hiddenInput);
        form.submit();
    }
</script>
@endsection