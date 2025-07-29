@extends('layouts/user/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/create.css')}}">
@endsection

@section('content')
<div class="attendance">
    <div class="attendance__inner">
        <div class="attendance__status-label">
            <a>{{ $status }}</a>
        </div>

        <div class="attendance__date">{{ $today }}</div>
        <div class="attendance__time">
            <span id="currentTime">{{ $currentTime }}</span>
        </div>

        <div class="attendance__buttons">
            @if ($isClockedOut)
                <p class="attendance__message">お疲れ様でした。</p>
            @else
                @if ($canClockIn)
                    <form action="{{ route('user.attendance.clockIn') }}" method="POST">@csrf
                        <button class="attendance__button-clockIn" type="submit">出勤</button>
                    </form>
                @endif

                <div class="attendance__buttons-inner">
                    @if ($canClockOut)
                        <form action="{{ route('user.attendance.clockOut') }}" method="POST">@csrf
                            <button class="attendance__button-clockOut" type="submit">退勤</button>
                        </form>
                    @endif

                    @if ($canBreakIn)
                        <form action="{{ route('user.attendance.breakIn') }}" method="POST">@csrf
                            <button class="attendance__button-breakIn" type="submit">休憩入</button>
                        </form>
                    @elseif ($canBreakOut)
                        <form action="{{ route('user.attendance.breakOut') }}" method="POST">@csrf
                            <button class="attendance__button-breakOut" type="submit">休憩戻</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const currentTimeEl = document.getElementById('currentTime');
    function updateTime() {
        const now = new Date();
        const hh = String(now.getHours()).padStart(2, '0');
        const mm = String(now.getMinutes()).padStart(2, '0');
        currentTimeEl.textContent = `${hh}:${mm}`;
    }
    updateTime();
    setInterval(updateTime, 1000);
});
</script>

@endsection