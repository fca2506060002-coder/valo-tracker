<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>VALO RR Tracker</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">

    <div class="max-w-6xl mx-auto py-10 px-4">

        <!-- タイトル -->
        <div class="flex justify-between items-center mb-8">

            <div>
                <h1 class="text-3xl font-bold">
                    VALO RR Tracker
                </h1>

                <p class="text-gray-500 mt-1">
                    VALORANT戦績管理
                </p>
            </div>

            <a
                href="{{ route('valo_matches.create') }}"
                class="bg-black text-white px-5 py-3 rounded-lg hover:bg-gray-800"
            >
                スクショを追加
            </a>

        </div>


        <!-- 登録成功メッセージ -->
        @if (session('success'))

            <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6">

                {{ session('success') }}

            </div>

        @endif


        <!-- 集計 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">

            <div class="bg-white rounded-xl shadow p-6">

                <p class="text-gray-500">
                    今日のRR
                </p>

                <p class="text-3xl font-bold mt-2">
                    {{ $todayRR >= 0 ? '+' : '' }}{{ $todayRR }}
                </p>

            </div>


            <div class="bg-white rounded-xl shadow p-6">

                <p class="text-gray-500">
                    今週のRR
                </p>

                <p class="text-3xl font-bold mt-2">
                    {{ $weekRR >= 0 ? '+' : '' }}{{ $weekRR }}
                </p>

            </div>


            <div class="bg-white rounded-xl shadow p-6">

                <p class="text-gray-500">
                    試合数
                </p>

                <p class="text-3xl font-bold mt-2">
                    {{ $matchCount }}
                </p>

            </div>

        </div>


        <!-- 勝敗 -->
        <div class="bg-white rounded-xl shadow p-6 mb-8">

            <h2 class="text-xl font-bold mb-4">
                勝敗
            </h2>

            <div class="flex gap-8">

                <div>
                    <p class="text-gray-500">
                        WIN
                    </p>

                    <p class="text-2xl font-bold">
                        {{ $winCount }}
                    </p>
                </div>


                <div>
                    <p class="text-gray-500">
                        LOSS
                    </p>

                    <p class="text-2xl font-bold">
                        {{ $lossCount }}
                    </p>
                </div>

            </div>

        </div>
        <div class="bg-white rounded-xl shadow p-6 mb-8">

<!-- RR推移 -->

<div class="bg-white rounded-xl shadow p-6 mb-8">

    <div class="flex justify-between items-center mb-4">

        <h2 class="text-xl font-bold">
            RR推移
        </h2>

        <select
            id="rrPeriod"
            class="border border-gray-300 rounded-lg px-3 py-2"
        >
            <option value="all">全期間</option>
            <option value="daily">日ごと</option>
            <option value="weekly">週ごと</option>
            <option value="monthly">月ごと</option>
        </select>

    </div>

    @if (count($allValues) > 0)

        <div class="relative h-80">

            <canvas
                id="rrChart"
                data-all-labels='@json($allLabels)'
                data-all-values='@json($allValues)'
                data-daily-labels='@json($dailyLabels)'
                data-daily-values='@json($dailyValues)'
                data-weekly-labels='@json($weeklyLabels)'
                data-weekly-values='@json($weeklyValues)'
                data-monthly-labels='@json($monthlyLabels)'
                data-monthly-values='@json($monthlyValues)'
            ></canvas>

        </div>

    @else

        <p class="text-gray-500">
            グラフに表示できる戦績がありません。
        </p>

    @endif

</div>


<!-- 戦績一覧 -->

<div class="bg-white rounded-xl shadow">

    <div class="p-6 border-b">

        <h2 class="text-xl font-bold">
            戦績一覧
        </h2>

    </div>

    @if ($matches->isEmpty())

        <div class="p-6 text-gray-500">

            まだ戦績がありません。

        </div>

    @else

        <div class="divide-y">

            @foreach ($matches as $match)

                <div class="p-5 flex justify-between items-center">

                    <div>

                        <p class="text-sm text-gray-500">
                            {{ $match->played_at->format('Y/m/d H:i') }}
                        </p>

                        <p class="font-bold mt-1">
                            {{ $match->result }}
                        </p>

                    </div>

                    <div class="text-2xl font-bold">

                        {{ $match->rr_change >= 0 ? '+' : '' }}{{ $match->rr_change }} RR

                    </div>

                </div>

            @endforeach

        </div>

    @endif

</div>
    </div>

</body>
</html>