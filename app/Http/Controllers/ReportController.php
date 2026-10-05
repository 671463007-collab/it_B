<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{

    public function myReports(Request $request)
    {
        $reports = Report::with([
            'reportedUser:id,name',
            'exchangePost:id,title',
        ])
            ->where('reporter_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json($reports);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'reported_user_id' => 'required|exists:users,id',
            'exchange_post_id' => 'nullable|exists:exchange_posts,id',
            'reason' => 'required|string',
        ], [
            'reported_user_id.required' => 'กรุณาเลือกผู้ใช้ที่ต้องการรายงาน',
            'reported_user_id.exists' => 'ไม่พบผู้ใช้ที่ต้องการรายงาน',
            'exchange_post_id.exists' => 'ไม่พบประกาศที่ต้องการรายงาน',
            'reason.required' => 'กรุณาบอกเหตุผลที่รายงาน',
        ]);


        if ($validated['reported_user_id'] == $request->user()->id) {
            return response()->json(['message' => 'รายงานตัวเองไม่ได้'], 422);
        }


        $report = new Report($validated);
        $report->reporter_id = $request->user()->id;
        $report->status = 'pending';  
        $report->save();

        return response()->json([
            'message' => 'ส่งรายงานแล้ว ทีมงานจะตรวจสอบให้',
            'report' => $report
        ], 201);
    }
}
