
@foreach ($items as $item)
    
<a href="{{route('stripe',$item->id)}}">Pay now {{$item->price}}</a>
@endforeach