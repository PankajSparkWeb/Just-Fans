@php
    $parts = explode('.', $asset);
    $extension = AttachmentHelper::getAttachmentType($parts ? $parts[count($parts) - 1] : false);
    $asset = Storage::url($asset);
    if(isset($attachment)){
        $asset = $attachment->path;
    }
@endphp

<a href="{{$asset}}" target="_blank">
    @switch($extension)
        @case('document')
            <img src="{{asset('/img/pdf-preview.svg')}}" class="admin-id-asset"/>
        @break
        @case('image')
            <img src="{{$asset}}" class="admin-id-asset"/>
        @break
        @case('video')
            <video class="video-preview w-75" src="{{$asset}}#t=0.001" controls controlsList="nodownload" preload="metadata"></video>
        @break
        @case('audio')
            <audio class="video-preview w-75" src="{{$asset}}#t=0.001" controls controlsList="nodownload" preload="metadata"></audio>
        @break
    @endswitch
</a>
