<?php

namespace App\Http\Controllers;

use App\Models\ValoMatch;
use Illuminate\Http\Request;

class ValoMatchController extends Controller
{
    public function create()
    {
        return view('valo_matches.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'screenshot' => 'required|image|mimes:jpg,jpeg,png|max:10240',
            'rr_values' => 'required|json',
        ]);

        $rrValues = json_decode($request->rr_values, true);

        if (!is_array($rrValues) || count($rrValues) === 0) {
            return back()->withErrors([
                'screenshot' => 'RRを検出できませんでした。',
            ]);
        }

        // スクショを保存
        $path = $request->file('screenshot')->store(
            'valo-screenshots',
            'public'
        );

        $savedCount = 0;

        foreach ($rrValues as $rr) {
            $rr = (int) $rr;

            // RRとして不自然な値は除外
            if ($rr < -50 || $rr > 50) {
                continue;
            }

            ValoMatch::create([
                'played_at' => now(),
                'result' => $rr >= 0 ? 'WIN' : 'LOSS',
                'rr_change' => $rr,
                'screenshot_path' => $path,
            ]);

            $savedCount++;
        }

        return back()->with(
            'success',
            "{$savedCount}試合の戦績をDBに登録しました！"
        );
    }
}