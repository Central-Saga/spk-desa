<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AksiAudit;
use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditTrail::query()
            ->with('user')
            ->orderByDesc('created_at');

        if ($keyword = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($keyword) {
                $q->where('deskripsi', 'like', "%{$keyword}%")
                    ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$keyword}%")
                        ->orWhere('name', 'like', "%{$keyword}%"));
            });
        }

        if ($aksi = $request->string('aksi')->trim()->toString()) {
            $query->where('aksi', $aksi);
        }

        if ($userId = $request->integer('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($from = $request->date('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->date('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $audit = $query->paginate(25)->withQueryString();

        return view('admin.audit-trail.index', [
            'audit' => $audit,
            'aksiOptions' => AksiAudit::cases(),
            'userOptions' => User::query()->orderBy('name')->get(['id', 'name', 'username']),
            'filters' => [
                'q' => $request->input('q'),
                'aksi' => $request->input('aksi'),
                'user_id' => $request->input('user_id'),
                'from' => $request->input('from'),
                'to' => $request->input('to'),
            ],
        ]);
    }

    public function show(AuditTrail $auditTrail): View
    {
        $auditTrail->load('user');

        return view('admin.audit-trail.show', [
            'audit' => $auditTrail,
        ]);
    }
}
