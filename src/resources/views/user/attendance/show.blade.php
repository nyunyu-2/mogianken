@extends('layouts/user/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/show.css')}}">
@endsection

@section('content')
<div class="attendance-detail">
    <div class="attendance-detail__title">勤怠詳細</div>
    <form class="attendance-detail__form" action="{{ route('user.application.resubmit') }}" method="POST" style="display:inline;">
        @csrf
        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
        <input type="hidden" name="application_id" value="{{ $application->id ?? '' }}">

        <div class="attendance-detail__table">
            <table class="attendance-detail__inner">
                <tr class="attendance-detail__row">
                    <th class="attendance-detail__header">名前</th>
                    <td class="attendance-detail__content-name">{{ $attendance->user->name }}</td>
                </tr>
                <tr class="attendance-detail__row">
                    <th class="attendance-detail__header">日付</th>
                    <td class="attendance-detail__content-date">
                        {{ \Carbon\Carbon::parse($attendance->date)->locale('ja')->isoFormat('YYYY年') }}
                        <span>{{ \Carbon\Carbon::parse($attendance->date)->locale('ja')->isoFormat('M月D日') }}</span>
                    </td>
                </tr>
                <tr class="attendance-detail__row">
                    <th class="attendance-detail__header">出勤・退勤</th>
                    <td class="attendance-detail__content">
                        @if(isset($application) && $application->is_pending)
                            {{-- 申請中（未承認）なら表示のみ --}}
                            <span class="attendance-detail__text-display">
                                {{ $attendance->formatted_clock_in_time }}
                            </span>
                            <span class="line"> ～ </span>
                            <span class="attendance-detail__text-display">
                                {{ $attendance->formatted_clock_out_time }}
                            </span>
                        @else
                            {{-- 承認済み or 申請なし なら編集可能 --}}
                            <input type="time" name="clock_in_time" value="{{ old('clock_in_time', $attendance->formatted_clock_in_time !== '-' ? $attendance->formatted_clock_in_time : '') }}" step="60">
                            <span class="line">～</span>
                            <input type="time" name="clock_out_time" value="{{ old('clock_out_time', $attendance->formatted_clock_out_time !== '-' ? $attendance->formatted_clock_out_time : '') }}" step="60">

                            @error('clock_in_time') <div class="error">{{ $message }}</div> @enderror
                            @error('clock_out_time') <div class="error">{{ $message }}</div> @enderror
                        @endif
                    </td>
                </tr>

                {{-- 休憩 --}}
                @php
                    $isPending = isset($application) && $application->status === '承認待ち';
                    $isEditing = !$isPending;

                    $allBreaks = $isPending
                        ? ($applicationBreaks ?? collect())
                        : ($application && $application->status === '承認済み'
                            ? $application->application_break_times
                            : ($attendance->breaks ?? collect())
                        );

                    $existingCount = $allBreaks->count();
                @endphp

                @foreach ($allBreaks as $i => $break)
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__header">休憩{{ $i + 1 }}</th>
                        <td class="attendance-detail__content">
                            @if ($isEditing)
                                <input type="time" name="breaks[{{ $i }}][start]"
                                    value="{{ old('breaks.' . $i . '.start', \Carbon\Carbon::parse($break->break_in_time)->format('H:i')) }}">
                                <span class="line">～</span>
                                <input type="time" name="breaks[{{ $i }}][end]"
                                    value="{{ old('breaks.' . $i . '.end', \Carbon\Carbon::parse($break->break_out_time)->format('H:i')) }}">
                            @else
                                <span class="attendance-detail__text-display">
                                    {{ \Carbon\Carbon::parse($break->break_in_time)->format('H:i') }}
                                </span>
                                <span class="line"> ～ </span>
                                <span class="attendance-detail__text-display">
                                    {{ \Carbon\Carbon::parse($break->break_out_time)->format('H:i') }}
                                </span>
                            @endif
                            @error('breaks.' . $i . '.start') <div class="error">{{ $message }}</div> @enderror
                            @error('breaks.' . $i . '.end') <div class="error">{{ $message }}</div> @enderror
                        </td>
                    </tr>
                @endforeach

                {{-- 追加入力欄 --}}
                @if ($isEditing)
                    @php
                        $breaksOld = old('breaks', []);
                        $maxOldIndex = count($breaksOld) > 0 ? max(array_keys($breaksOld)) : -1;
                        $nextIndex = max($existingCount, $maxOldIndex + 1);
                    @endphp

                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__header">休憩{{ $nextIndex + 1 }}</th>
                        <td class="attendance-detail__content">
                            <input type="time" name="breaks[{{ $nextIndex }}][start]" value="{{ old('breaks.' . $nextIndex . '.start') }}">
                            <span class="line">～</span>
                            <input type="time" name="breaks[{{ $nextIndex }}][end]" value="{{ old('breaks.' . $nextIndex . '.end') }}">
                            @error('breaks.' . $nextIndex . '.start') <div class="error">{{ $message }}</div> @enderror
                            @error('breaks.' . $nextIndex . '.end') <div class="error">{{ $message }}</div> @enderror
                        </td>
                    </tr>
                @endif

                {{-- 備考 --}}
                <tr class="attendance-detail__row">
                    <th class="attendance-detail__header">備考</th>
                    <td class="attendance-detail__content-text">
                        @if(!isset($application) || !$application->is_pending)
                            <textarea name="reason" id="reason" required>{{ old('reason', $application->reason ?? '') }}</textarea>
                        @else
                            <p class="attendance-detail__textarea-display">{{ $application->reason }}</p>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        @if (isset($application) && $application->is_pending)
            <div class="attendance-detail__notice">
                *承認待ちのため修正はできません。
            </div>
        @else
            <div class="attendance-detail__button">
                <button type="submit" class="btn">修正</button>
            </div>
        @endif
    </form>
</div>
@endsection