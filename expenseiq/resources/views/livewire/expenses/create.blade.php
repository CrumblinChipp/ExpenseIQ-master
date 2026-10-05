<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Expense</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-100">

<div class="max-w-2xl mx-auto mt-10 bg-white p-8 rounded-lg shadow">

    <h1 class="text-3xl font-bold mb-8">
        Add Expense
    </h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 p-4 rounded mb-6">
            <ul>
                @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('expenses.store') }}" method="POST">

        @csrf

        <div class="mb-5">

            <label class="font-semibold">
                Category
            </label>

            <select
                name="category_id"
                class="w-full border rounded-lg p-3 mt-2">

                <option value="">Select Category</option>

                @foreach($categories as $category)

                    <option
                        value="{{ $category->id }}">

                        {{ $category->category_name }}

                    </option>

                @endforeach

            </select>

        </div>

        <div class="mb-5">

            <label class="font-semibold">
                Description
            </label>

            <input
                type="text"
                name="description"
                class="w-full border rounded-lg p-3 mt-2">

        </div>

        <div class="mb-5">

            <label class="font-semibold">
                Amount
            </label>

            <input
                type="number"
                step="0.01"
                name="amount"
                class="w-full border rounded-lg p-3 mt-2">

        </div>

        <div class="mb-5">

            <label class="font-semibold">
                Expense Date
            </label>

            <input
                type="date"
                name="expense_date"
                class="w-full border rounded-lg p-3 mt-2">

        </div>

        <div class="mb-6">

            <label class="font-semibold">
                Notes
            </label>

            <textarea
                name="notes"
                rows="4"
                class="w-full border rounded-lg p-3 mt-2"></textarea>

        </div>

        <button
            class="bg-blue-600 text-white px-8 py-3 rounded-lg">

            Save Expense

        </button>

    </form>

</div>

</body>
</html>