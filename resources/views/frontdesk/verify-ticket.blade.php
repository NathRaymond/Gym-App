@extends('layouts.app')
@section('title', 'Verify Ticket')

@section('content')
<div class="mx-auto max-w-2xl px-4 py-10" x-data="verifyTicket()">

    <div class="mb-6">
        <h1 class="text-2xl font-bold">Verify Ticket</h1>
        <p class="text-sm text-gray-500">Enter the code or scan the QR to check in a member</p>
    </div>

    {{-- Verification Card --}}
    <div class="rounded-2xl bg-white border border-gray-200 p-6">

        {{-- Input Form --}}
        <form @submit.prevent="verify()">
            <label class="text-sm font-medium">Ticket Code</label>

            <div class="flex gap-2 mt-1">
                <input type="text" x-model="code" x-ref="codeInput"
                       placeholder="TKT-XXXXXXXXXX"
                       class="flex-1 rounded-lg border-gray-300 border px-3 py-2.5 font-mono focus:border-brand-500 focus:ring-brand-500"
                       required>
                <button type="submit"
                        :disabled="loading || !code"
                        class="rounded-lg bg-brand-600 px-6 py-2.5 font-semibold text-white hover:bg-brand-700 disabled:opacity-50">
                    <span x-show="!loading">Verify</span>
                    <span x-show="loading">...</span>
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-2">
                Tip: Paste or type the ticket code, then press Enter
            </p>
        </form>

        {{-- Success Result --}}
        <div x-show="result && result.valid" x-cloak
             x-transition
             class="mt-6 rounded-xl border-2 border-green-300 bg-green-50 p-4">
            <div class="flex items-start gap-3">
                <div class="text-3xl">✅</div>
                <div class="flex-1">
                    <div class="font-bold text-green-800 text-lg">Access Granted</div>
                    <div class="text-sm text-green-700 mt-2 space-y-1">
                        <div><strong>User:</strong> <span x-text="(result?.ticket?.user?.first_name || '') + ' ' + (result?.ticket?.user?.last_name || '')"></span></div>
                        <div><strong>Type:</strong> <span x-text="result?.ticket?.type"></span></div>
                        <div><strong>Code:</strong> <span class="font-mono" x-text="result?.ticket?.ticket_code"></span></div>
                        <div><strong>Valid until:</strong> <span x-text="formatDate(result?.ticket?.valid_until)"></span></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Error Result --}}
        <div x-show="result && !result.valid" x-cloak
             x-transition
             class="mt-6 rounded-xl border-2 border-red-300 bg-red-50 p-4">
            <div class="flex items-start gap-3">
                <div class="text-3xl">❌</div>
                <div class="flex-1">
                    <div class="font-bold text-red-800 text-lg">Access Denied</div>
                    <div class="text-sm text-red-700 mt-1" x-text="result?.message"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="mt-6 rounded-2xl bg-white border border-gray-200 p-6">
        <h2 class="font-semibold mb-3">Today's Check-ins</h2>
        <div x-show="recent.length === 0" class="text-sm text-gray-500 text-center py-4">
            No tickets verified yet today.
        </div>
        <div class="space-y-2">
            <template x-for="(r, i) in recent" :key="i">
                <div class="flex justify-between items-center py-2 border-b last:border-0 text-sm">
                    <div>
                        <div class="font-medium" x-text="r.user"></div>
                        <div class="text-xs text-gray-500 font-mono" x-text="r.code"></div>
                    </div>
                    <div class="text-xs text-green-600">✓ Verified</div>
                </div>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script>
function verifyTicket() {
    return {
        code: '',
        loading: false,
        result: null,
        recent: [],

        async verify() {
            if (!this.code) return;

            this.loading = true;
            this.result = null;

            try {
                const res = await fetch('/api/tickets/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ticket_code: this.code })
                });

                const data = await res.json();
                this.result = data;

                // Play a sound + SweetAlert
                if (data.valid) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Access Granted ✅',
                        html: `<strong>${(data.ticket?.user?.first_name || '')} ${(data.ticket?.user?.last_name || '')}</strong><br>
                               <span style="font-size:12px;color:#666;">${data.ticket?.type} · ${data.ticket?.ticket_code}</span>`,
                        timer: 2500,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        position: 'center',
                    });

                    // Add to recent list
                    this.recent.unshift({
                        user: (data.ticket?.user?.first_name || '') + ' ' + (data.ticket?.user?.last_name || ''),
                        code: data.ticket?.ticket_code,
                        time: new Date().toLocaleTimeString(),
                    });

                    // Keep only last 10
                    if (this.recent.length > 10) this.recent.pop();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Access Denied',
                        text: data.message || 'Ticket invalid or expired',
                        confirmButtonColor: '#dc2626',
                    });
                }

                // Clear code for next scan
                this.code = '';
                this.$refs.codeInput.focus();

            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message,
                });
            } finally {
                this.loading = false;
            }
        },

        formatDate(d) {
            if (!d) return '—';
            return new Date(d).toLocaleString('en-NG');
        }
    }
}
</script>
@endpush
@endsection