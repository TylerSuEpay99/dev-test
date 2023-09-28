info
@foreach($info as $item)
<p>{{ $item['name'] }} <span> {{$item['info']}} </span></p>
@endforeach