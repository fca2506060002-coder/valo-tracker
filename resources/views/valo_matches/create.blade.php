<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VALO戦績スクショ解析</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">

    <div class="max-w-2xl mx-auto py-12 px-4">

        <div class="bg-white rounded-xl shadow p-8">

            <h1 class="text-3xl font-bold mb-2">
                VALO戦績スクショ解析
            </h1>

            <p class="text-gray-600 mb-8">
                VALORANTの戦績スクショをアップロードしてください。
            </p>

            @if (session('success'))
                <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                action="{{ route('valo_matches.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                <label class="block font-semibold mb-2">
                    戦績スクショ
                </label>

                <input
    type="file"
    id="screenshot"
    name="screenshot"
    accept="image/jpeg,image/png"
    class="w-full border border-gray-300 rounded-lg p-3 mb-6"
    required
>
<input
    type="hidden"
    id="rr-values"
    name="rr_values"
    value=""
>

<div class="mb-6">
    <p id="ocr-status" class="font-semibold text-gray-700 mb-2">
        スクショを選択するとOCR解析を開始します。
    </p>

    <pre
        id="ocr-result"
        class="bg-gray-100 p-4 rounded-lg whitespace-pre-wrap text-sm"
    ></pre>
</div>

                <button
                    type="submit"
                    class="w-full bg-black text-white py-3 rounded-lg hover:bg-gray-800"
                >
                    スクショをアップロード
                </button>
            </form>

        </div>

    </div>

</body>
</html>