<style>
.drawer-header {
    background: #fff;
    border-bottom: 1px solid #e6e9ef;
    padding: 12px 16px;
}

/* Title */
.drawer-title {
    font-weight: 600;
    color: #0c2957;
}

/* Sub text */
.drawer-meta {
    font-size: 12px;
    color: #8b95a7;
}

/* ZIP badge */
.zip-count {
    background: rgba(252, 82, 0, 0.1);
    color: #fc5200;
    font-size: 12px;
    border-radius: 6px;
    padding: 0px 8px;
}

/* ZIP tags */
.zip-tags span {
    background: #f1f3f6;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    margin-right: 4px;
}

.zip-more-btn {
    background: none;
    border: none;
    color: #fc5200;
    font-size: 12px;
    cursor: pointer;
    margin-left: 4px;
}

</style>

<div class="drawer-header">
    @php
        $zips = !empty($activity->passed_zips)
            ? (is_array($activity->passed_zips) ? $activity->passed_zips : json_decode($activity->passed_zips, true))
            : [];
    @endphp

    <!-- LINE 1 -->
    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">

        <!-- Title -->
        <span class="drawer-title">
            {{ $activity->name }}
        </span>

        <!-- Time -->
        <span class="drawer-meta">
            <i class="bi bi-clock me-1"></i>
            {{ \Carbon\Carbon::parse($activity->date)->format('D, M d H:i') }}
        </span>

        <!-- Divider -->
        <span class="drawer-meta">|</span>

        <!-- ZIP Count -->
        <span class="zip-count">
            {{ count($zips) }} ZIPs
        </span>

    </div>

    <!-- ZIP List -->
    <div class="col text-truncate">
        <div class="zip-tags" id="zipTags">
            @foreach($zips as $index => $zip)
                <span class="zip-item {{ $index >= 5 ? 'd-none extra-zip' : '' }}" data-zip="{{$zip}}">
                    {{ $zip }}
                </span>
            @endforeach
    
            @if(count($zips) > 5)
                <button class="zip-more-btn" onclick="toggleZips()">
                    +{{ count($zips) - 5 }} more
                </button>
            @endif
        </div>
    </div>
</div>

<script>
function toggleZips() {
    const hiddenZips = document.querySelectorAll('.extra-zip');
    hiddenZips.forEach(el => el.classList.toggle('d-none'));

    const btn = document.querySelector('.zip-more-btn');
    btn.textContent = btn.textContent.includes('more') ? 'Show less' : `+${hiddenZips.length} more`;
}
</script>