@foreach($zips as $zip)
    <div class="col-md-2 zip-item">
        <div class="zip-card p-1 rounded bg-light">
            <a class="open-map-drawer" style="cursor:pointer" data-id="{{$zip->zip_code}}">
                <div class="zip-badge px-1 py-1 rounded bg-light text-center">
                    {{ $zip->zip_code }}
                </div>
            </a>
        </div>
    </div>
@endforeach