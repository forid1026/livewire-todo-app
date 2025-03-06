<?php

namespace App\Livewire;

use App\Models\Todo;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session as FacadesSession;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class TodoList extends Component
{
    use WithPagination;
    #[Rule('required|min:3|max:50')]
    public $name;
    public $search;
    public $editingTodoId;

    #[Rule('required|min:3|max:50')]
    public $editingTodoName;

    public function create(){
        $validated= $this->validateOnly('name');
        Todo::create($validated);

        $this->reset('name');
        $this->resetPage();
        session()->flash('success','Created');
    }

    public function delete($id){
        try {
            Todo::findOrFail($id)->delete();
            session()->flash('deleted','Deleted');
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
            Log::error('Error deleting task: ' . $th->getMessage());
            session()->flash('error','Error deleting task');
        }
    }

    public function toggle($id){
        $todo = Todo::findOrFail($id);
        $todo->completed = !$todo->completed;
        $todo->save();
    }

    public function edit($id){
        $todo = Todo::findOrFail($id);
        $this->editingTodoId = $todo->id;
        $this->editingTodoName = $todo->name;

    }
    public function update(){
        $validated= $this->validateOnly('editingTodoName');
        $todo = Todo::findOrFail($this->editingTodoId);
        $todo->name = $this->editingTodoName;
        $todo->updated_at = now();
        $todo->save();
        $this->cancel();
    }

    public function cancel(){
        $this->reset('editingTodoId', 'editingTodoName');
    }
    public function render()
    {
        return view('livewire.todo-list',[
            'todos' => Todo::latest()->where('name','like',"%{$this->search}%")->paginate(5)
        ]);
    }
}
