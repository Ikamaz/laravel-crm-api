<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NextTech CRM — Enquiries</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#0b1220;color:#f4f7fb;font:14px/1.5 system-ui,sans-serif}.wrap{width:min(1180px,calc(100% - 36px));margin:auto}.top{border-bottom:1px solid #ffffff16;padding:20px 0;display:flex;align-items:center;justify-content:space-between;gap:20px}.brand{font-weight:800;letter-spacing:-.04em;font-size:18px}.brand span{color:#67a8ff}.muted{color:#9aabc1}.btn{border:1px solid #344963;background:#15243a;color:#dce8f5;padding:9px 13px;border-radius:8px;font:inherit;cursor:pointer}.main{padding:35px 0}.heading{display:flex;justify-content:space-between;align-items:end;gap:20px;margin-bottom:22px}h1{font-size:30px;letter-spacing:-.04em;margin:0}select{background:#101d2e;border:1px solid #344963;color:white;border-radius:8px;padding:9px}.tablebox{overflow:auto;border:1px solid #293b53;border-radius:14px;background:#111c2e}table{border-collapse:collapse;width:100%;min-width:850px}th,td{text-align:left;padding:14px 15px;border-bottom:1px solid #26364d;vertical-align:top}th{font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#9aabc1}td{font-size:13px}tr:last-child td{border-bottom:0}.contact{font-weight:700}.sub{display:block;color:#9aabc1;font-size:12px;margin-top:3px}.status{display:flex;gap:7px;align-items:center}.status select{padding:6px;font-size:12px}.save{padding:7px 9px;font-size:12px}.notice{padding:12px 14px;background:#10352e;border:1px solid #347d69;color:#baf3df;border-radius:9px;margin-bottom:15px}.empty{padding:40px;text-align:center;color:#9aabc1}.pagination{padding:16px;display:flex;justify-content:space-between;color:#9aabc1}.pagination a{color:#b8d8ff}.message{max-width:260px;white-space:pre-wrap;overflow-wrap:anywhere;color:#bdcadd}
    </style>
</head>
<body>
<header class="top wrap">
    <div><div class="brand">Next<span>Tech</span></div><div class="muted">NextTech / Enquiries</div></div>
    <form method="POST" action="{{ route('agency.admin.logout') }}">@csrf<button class="btn" type="submit">Sign out</button></form>
</header>
<main class="wrap main">
    <div class="heading">
        <div><h1>Inbound enquiries</h1><div class="muted">Review new requests and update their pipeline status.</div></div>
        <form method="GET" action="{{ route('agency.admin.leads') }}">
            <select name="status" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach(['new','contacted','qualified','converted','closed'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </form>
    </div>
    @if(session('status_updated'))<div class="notice" role="status">Lead status updated.</div>@endif
    <section class="tablebox">
        @if($leads->isEmpty())
            <div class="empty">No enquiries found for this filter.</div>
        @else
            <table>
                <thead><tr><th>Contact</th><th>Company</th><th>Requested service</th><th>Message</th><th>Received</th><th>Pipeline</th></tr></thead>
                <tbody>
                @foreach($leads as $lead)
                    <tr>
                        <td><span class="contact">{{ $lead->name }}</span><span class="sub">{{ $lead->email ?? 'No email' }}</span><span class="sub">{{ $lead->phone ?? 'No phone' }}</span></td>
                        <td>{{ $lead->company ?: '—' }}</td>
                        <td>{{ str($lead->service)->replace('-', ' ')->title() }}<span class="sub">{{ strtoupper($lead->locale) }}</span></td>
                        <td class="message">{{ $lead->message ?: '—' }}</td>
                        <td>{{ $lead->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <form class="status" method="POST" action="{{ route('agency.admin.leads.update', $lead) }}">
                                @csrf
                                @method('PATCH')
                                <select name="status" aria-label="Lead status">
                                    @foreach(['new','contacted','qualified','converted','closed'] as $status)
                                        <option value="{{ $status }}" @selected($lead->status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                <button class="btn save" type="submit">Save</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="pagination"><span>Page {{ $leads->currentPage() }} of {{ $leads->lastPage() }}</span><span>@if($leads->previousPageUrl())<a href="{{ $leads->previousPageUrl() }}">← Previous</a>@endif &nbsp; @if($leads->nextPageUrl())<a href="{{ $leads->nextPageUrl() }}">Next →</a>@endif</span></div>
        @endif
    </section>
</main>
</body>
</html>
