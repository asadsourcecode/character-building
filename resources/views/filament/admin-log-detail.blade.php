<div class="space-y-4 text-sm">

    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-gray-500 font-medium">Admin</p>
            <p>{{ $record->causer?->name ?? '—' }} ({{ $record->causer?->email ?? '—' }})</p>
        </div>
        <div>
            <p class="text-gray-500 font-medium">Date & Time</p>
            <p>{{ $record->created_at->setTimezone('Asia/Karachi')->format('d M Y, h:i A') }}</p>
        </div>
        <div>
            <p class="text-gray-500 font-medium">Action</p>
            <p class="capitalize font-semibold">{{ $record->event }}</p>
        </div>
        <div>
            <p class="text-gray-500 font-medium">Resource</p>
            <p>{{ ucfirst(str_replace('_', ' ', $record->log_name)) }} #{{ $record->subject_id }}</p>
        </div>
    </div>

    @php
        $skip = ['updated_at', 'created_at'];
        $old  = collect($record->properties['old'] ?? [])->except($skip)->toArray();
        $new  = collect($record->properties['attributes'] ?? [])->except($skip)->toArray();
        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));

        $fmt = function ($v) {
            if (is_null($v))  return '—';
            if (is_array($v)) return json_encode($v);
            // format ISO datetime strings nicely
            if (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $v)) {
                try {
                    return \Carbon\Carbon::parse($v)->setTimezone('Asia/Karachi')->format('d M Y, h:i A');
                } catch (\Throwable $e) {}
            }
            return (string) $v;
        };
    @endphp

    @if(count($keys))
    <div class="border-t pt-4 mt-4">
        <p class="font-semibold text-gray-700 mb-3">Field Changes</p>
        <table style="width:100%; border-collapse:collapse; font-size:12px; table-layout:fixed;">
            <colgroup>
                <col style="width:20%">
                <col style="width:40%">
                <col style="width:40%">
            </colgroup>
            <thead>
                <tr style="background:#f3f4f6;">
                    <th style="text-align:left; padding:8px; border:1px solid #d1d5db;">Field</th>
                    <th style="text-align:left; padding:8px; border:1px solid #d1d5db; color:#b91c1c;">Before</th>
                    <th style="text-align:left; padding:8px; border:1px solid #d1d5db; color:#15803d;">After</th>
                </tr>
            </thead>
            <tbody>
                @foreach($keys as $key)
                <tr>
                    <td style="padding:8px; border:1px solid #d1d5db; font-weight:600; word-break:break-word;">{{ $key }}</td>
                    <td style="padding:8px; border:1px solid #d1d5db; color:#b91c1c; word-break:break-word;">{{ $fmt($old[$key] ?? null) }}</td>
                    <td style="padding:8px; border:1px solid #d1d5db; color:#15803d; word-break:break-word;">{{ $fmt($new[$key] ?? null) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>
