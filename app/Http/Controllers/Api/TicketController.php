<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $q = Ticket::with(['user', 'booking', 'membership']);
        if (!$request->user()->isStaff()) $q->where('user_id', $request->user()->id);
        if ($request->filled('status')) $q->where('status', $request->status);
        return response()->json($q->latest()->paginate($request->get('per_page', 15)));
    }

    public function verify(Request $request)
    {
        $request->validate(['ticket_code' => 'required|string']);

        $ticket = Ticket::where('ticket_code', $request->ticket_code)
            ->with(['user', 'booking', 'membership'])
            ->first();

        if (!$ticket) {
            return response()->json(['valid' => false, 'message' => 'Ticket not found.'], 404);
        }

        $valid = $ticket->isValid();

        if ($valid) {
            $ticket->update([
                'status' => $ticket->type === 'booking' ? 'used' : 'active',
                'used_at' => now(),
                'verified_by' => $request->user()->id,
            ]);
            ActivityLogger::log('Ticket verified & checked in', $ticket, ['ticket_code' => $ticket->ticket_code]);
        }

        return response()->json([
            'valid' => $valid,
            'ticket' => $ticket->fresh(),
            'message' => $valid ? 'Ticket valid. Access granted.' : 'Ticket expired or inactive.',
        ]);
    }
}