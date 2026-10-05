<?php

namespace App\Livewire\Expenses;

use Livewire\Component;
use App\Models\Expense;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    // Form Fields

    public $category_id = '';
    public $description = '';
    public $amount = '';
    public $expense_date = '';
    public $notes = '';


    // IDs

    public $editingExpenseId = null;
    public $deleteExpenseId = null;

   
    // Modal States
 
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showUnsavedModal = false;
    public $showEditUnsavedModal = false;

    public function mount()
    {
        $this->expense_date = now()->toDateString();
    }

    protected function rules()
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
        ];
    }

  
    //  CREATE
   

    public function saveExpense()
    {
        $this->validate();

        Expense::create([
            'user_id' => Auth::id(),
            'category_id' => $this->category_id,
            'description' => $this->description,
            'amount' => $this->amount,
            'expense_date' => $this->expense_date,
            'notes' => $this->notes,
        ]);

        session()->flash('success', 'Expense added successfully.');

        $this->resetForm();
    }

  
    //  EDIT


    public function editExpense($id)
    {
        $expense = Expense::where('user_id', Auth::id())
            ->findOrFail($id);

        $this->editingExpenseId = $expense->id;

        $this->category_id = $expense->category_id;
        $this->description = $expense->description;
        $this->amount = $expense->amount;
        $this->expense_date = $expense->expense_date;
        $this->notes = $expense->notes;

        $this->showEditModal = true;
    }

    public function updateExpense()
    {
        $this->validate();

        Expense::where('user_id', Auth::id())
            ->findOrFail($this->editingExpenseId)
            ->update([
                'category_id' => $this->category_id,
                'description' => $this->description,
                'amount' => $this->amount,
                'expense_date' => $this->expense_date,
                'notes' => $this->notes,
            ]);

        session()->flash('success', 'Expense updated successfully.');

        $this->showEditModal = false;
        $this->editingExpenseId = null;

        $this->resetForm();
    }

    public function cancelEdit()
    {
        $this->showEditModal = false;
        $this->showEditUnsavedModal = false;
        $this->editingExpenseId = null;

        $this->resetForm();
    }

    public function discardEditChanges()
    {
        $this->showEditUnsavedModal = false;
        $this->showEditModal = false;
        $this->editingExpenseId = null;

        $this->resetForm();
    }

  
    //  DELETE
  

    public function confirmDelete($id)
    {
        $this->deleteExpenseId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteExpense()
    {
        Expense::where('user_id', Auth::id())
            ->findOrFail($this->deleteExpenseId)
            ->delete();

        $this->deleteExpenseId = null;
        $this->showDeleteModal = false;

        session()->flash('success', 'Expense deleted successfully.');
    }

    
    //  UNSAVED MODALS
 

    public function closeUnsavedModal()
    {
        $this->showUnsavedModal = false;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteExpenseId = null;
    }

    
    //  RESET FORM
   

    private function resetForm()
    {
        $this->reset([
            'category_id',
            'description',
            'amount',
            'notes',
        ]);

        $this->expense_date = now()->toDateString();

        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showUnsavedModal = false;
        $this->showEditUnsavedModal = false;
    }

   
    //  RENDER
   

    public function render()
    {
        return view('livewire.expenses.index', [
            'expenses' => Expense::with('category')
                ->where('user_id', Auth::id())
                ->latest()
                ->get(),

            'categories' => Category::orderBy('category_name')->get(),
        ])->layout('layouts.app');
    }
}