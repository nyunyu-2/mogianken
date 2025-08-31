@extends('layouts/admin/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/show.css')}}">
@endsection

@section('content')
<div class="admin-attendance-detail">
    <div class="admin-attendance-detail__title">勤怠詳細</div>
    <form class="admin-attendance-detail__form" action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST" style="display:inline;">
        @csrf
        @method('PUT')
        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
        <input type="hidden" name="application_id" value="{{ $application->id ?? '' }}">

        <div class="admin-attendance-detail__table">
            <table class="admin-attendance-detail__inner">
                <tr class="admin-attendance-detail__row">
                    <th class="admin-attendance-detail__header">名前</th>
                    <td class="admin-attendance-detail__content-name">{{ $attendance->user->name }}</td>
                </tr>
                <tr class="admin-attendance-detail__row">
                    <th class="admin-attendance-detail__header">日付</th>
                    <td class="admin-attendance-detail__content-date">
                        {{ \Carbon\Carbon::parse($attendance->date)->locale('ja')->isoFormat('YYYY年') }}
                        <span>{{ \Carbon\Carbon::parse($attendance->date)->locale('ja')->isoFormat('M月D日') }}</span>
                    </td>
                </tr>
                <tr class="admin-attendance-detail__row">
                    <th class="admin-attendance-detail__header">出勤・退勤</th>
                    <td class="admin-attendance-detail__content">
                        @if(isset($application) && $application->is_pending)
                            {{-- 申請中（未承認）なら表示のみ --}}
                            <span class="admin-attendance-detail__text-display">
                                {{ $application->formatted_clock_in_time }}
                            </span>
                            <span class="line"> ～ </span>
                            <span class="admin-attendance-detail__text-display">
                                {{ $application->formatted_clock_out_time }}
                            </span>
                        @else
                            {{-- 承認済み or 申請なし なら編集可能 --}}
                            <input type="time" name="clock_in_time" value="{{ old('clock_in_time', $attendance->formatted_clock_in_time !== '-' ? $attendance->formatted_clock_in_time : '') }}" step="60">
                            <span class="line">～</span>
                            <input type="time" name="clock_out_time" value="{{ old('clock_out_time', $attendance->formatted_clock_out_time !== '-' ? $attendance->formatted_clock_out_time : '') }}" step="60">

                            @if($errors->has('clock_in_time') || $errors->has('clock_out_time'))
                                <div class="error">
                                    {{ $errors->first('clock_in_time') ?: $errors->first('clock_out_time') }}
                                </div>
                            @endif
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
                    <tr class="admin-attendance-detail__row">
                        <th class="admin-attendance-detail__header">休憩{{ $i + 1 }}</th>
                        <td class="admin-attendance-detail__content">
                            @if ($isEditing)
                                <input type="time" name="breaks[{{ $i }}][start]"
                                    value="{{ old('breaks.' . $i . '.start', \Carbon\Carbon::parse($break->break_in_time)->format('H:i')) }}">
                                <span class="line">～</span>
                                <input type="time" name="breaks[{{ $i }}][end]"
                                    value="{{ old('breaks.' . $i . '.end', \Carbon\Carbon::parse($break->break_out_time)->format('H:i')) }}">
                            @else
                                <span class="admin-attendance-detail__text-display">
                                    {{ \Carbon\Carbon::parse($break->break_in_time)->format('H:i') }}
                                </span>
                                <span class="line"> ～ </span>
                                <span class="admin-attendance-detail__text-display">
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

                    <tr class="admin-attendance-detail__row">
                        <th class="admin-attendance-detail__header">休憩{{ $nextIndex + 1 }}</th>
                        <td class="admin-attendance-detail__content">
                            <input type="time" name="breaks[{{ $nextIndex }}][start]" value="{{ old('breaks.' . $nextIndex . '.start') }}">
                            <span class="line">～</span>
                            <input type="time" name="breaks[{{ $nextIndex }}][end]" value="{{ old('breaks.' . $nextIndex . '.end') }}">
                            @error('breaks.' . $nextIndex . '.start')
                                <div class="error">{{ $message }}</div>
                            @enderror
                            @error('breaks.' . $nextIndex . '.end')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                @endif

                <tr class="admin-attendance-detail__row">
                    <th class="admin-attendance-detail__header">備考</th>
                    <td class="admin-attendance-detail__content-text">
                        @if(!isset($application) || !$application->is_pending)
                            <textarea name="reason" id="reason">{{ old('reason', $application->reason ?? '') }}</textarea>
                            @error('reason')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        @else
                            <p class="admin-attendance-detail__textarea-display">{{ $application->reason }}</p>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        <div class="admin-attendance-detail__button">
            <button type="submit" class="btn">修正</button>
        </div>
    </form>
</div>
@endsection