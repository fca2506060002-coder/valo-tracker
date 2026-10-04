<?php

namespace App\Http\Controllers;

use App\Models\ValoMatch;
use Illuminate\Http\Request;

class ValoMatchController extends Controller
{
public function index()
{
    $matches = ValoMatch::whereIn('result', ['WIN', 'LOSS'])
        ->latest('played_at')
        ->get();

    // 今日のRR
    $todayRR = ValoMatch::whereIn('result', ['WIN', 'LOSS'])
        ->whereDate('played_at', today())
        ->sum('rr_change');

    // 今週のRR
    $weekRR = ValoMatch::whereIn('result', ['WIN', 'LOSS'])
        ->whereBetween('played_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ])
        ->sum('rr_change');

    $matchCount = $matches->count();

    $winCount = $matches->where('result', 'WIN')->count();
    $lossCount = $matches->where('result', 'LOSS')->count();

    /*
     * 全期間
     * 1試合ごとの累積RR
     */
    $chartMatches = $matches
        ->reverse()
        ->values();

    $cumulativeRR = 0;
    $allLabels = [];
    $allValues = [];

    foreach ($chartMatches as $match) {
        $cumulativeRR += $match->rr_change;

        $allLabels[] = $match->played_at->format('m/d H:i');
        $allValues[] = $cumulativeRR;
    }

    /*
     * 日ごと
     */
    $daily = $matches
        ->groupBy(fn ($match) => $match->played_at->format('Y-m-d'))
        ->sortKeys();

    $dailyLabels = [];
    $dailyValues = [];

    foreach ($daily as $date => $dayMatches) {
        $dailyLabels[] = $date;
        $dailyValues[] = $dayMatches->sum('rr_change');
    }

    /*
     * 週ごと
     */
    $weekly = $matches
        ->groupBy(fn ($match) =>
            $match->played_at->copy()->startOfWeek()->format('Y-m-d')
        )
        ->sortKeys();

    $weeklyLabels = [];
    $weeklyValues = [];

    foreach ($weekly as $week => $weekMatches) {
        $weeklyLabels[] = $week;
        $weeklyValues[] = $weekMatches->sum('rr_change');
    }

    /*
     * 月ごと
     */
    $monthly = $matches
        ->groupBy(fn ($match) => $match->played_at->format('Y-m'))
        ->sortKeys();

    $monthlyLabels = [];
    $monthlyValues = [];

    foreach ($monthly as $month => $monthMatches) {
        $monthlyLabels[] = $month;
        $monthlyValues[] = $monthMatches->sum('rr_change');
    }

    return view('valo_matches.index', compact(
        'matches',
        'todayRR',
        'weekRR',
        'matchCount',
        'winCount',
        'lossCount',
        'allLabels',
        'allValues',
        'dailyLabels',
        'dailyValues',
        'weeklyLabels',
        'weeklyValues',
        'monthlyLabels',
        'monthlyValues'
    ));
}

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

        $path = $request->file('screenshot')->store(
            'valo-screenshots',
            'public'
        );

        $savedCount = 0;

        foreach ($rrValues as $rr) {
            $rr = (int) $rr;

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

        return redirect()
            ->route('valo_matches.index')
            ->with(
                'success',
                "{$savedCount}試合の戦績をDBに登録しました！"
            );
    }
}