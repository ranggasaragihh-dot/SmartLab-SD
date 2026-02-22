<?php

namespace App\Livewire\Admin;

use App\Models\Cluster;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.admin')]
class ClusterManager extends Component
{
    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    public $image_upload; 

    public $isEdit = false;
    public $editId = null;
    public $showFormModal = false;

    public $showDeleteModal = false;
    public $deleteId;
    public $deleteName;

    public $form = [
        'name' => '',
        'type' => 'public', 
        'description' => '',
        'is_active' => true,
        'delay_time' => 15,
        'denah_image' => null,
        'min_duration_minutes' => 30,
        'max_duration_minutes' => 120,
    ];

    public function render()
    {
        return view('livewire.admin.cluster-manager', [
            'clusters' => Cluster::orderBy('name', 'asc')->paginate(10) 
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->isEdit = false;
        $this->showFormModal = true;
    }

    public function edit($id)
    {
        $data = Cluster::findOrFail($id);
        $this->editId = $id;
        $this->isEdit = true;

        $this->form = [
            'name' => $data->name,
            'type' => $data->type,
            'description' => $data->description,
            'is_active' => $data->is_active ? 1 : 0, 
            'delay_time' => $data->delay_time ?? 15,
            'denah_image' => $data->denah_image,
            'min_duration_minutes' => $data->min_duration_minutes ?? 30, 
            'max_duration_minutes' => $data->max_duration_minutes ?? 120, 
        ];

        $this->showFormModal = true;
    }

    public function store()
    {
        $this->validate([
            'form.name' => 'required|string|max:50',
            'form.type' => 'required|in:public,private',
            'form.description' => 'nullable|string|max:255',
            'form.is_active' => 'boolean',
            'form.delay_time' => 'required|integer|min:1',
            'form.min_duration_minutes' => 'required|integer|min:1',
            'form.max_duration_minutes' => 'required|integer|min:1|gt:form.min_duration_minutes',
            'image_upload' => 'nullable|image|max:2048',
        ],[
            'form.max_duration_minutes.gt' => 'Durasi maksimal harus lebih besar dari durasi minimal.'
        ]);

        $imagePath = $this->isEdit ? $this->form['denah_image'] : null;
        if ($this->image_upload) {
            if ($this->isEdit && !empty($this->form['denah_image'])) {
                if (Storage::disk('public')->exists($this->form['denah_image'])) {
                    Storage::disk('public')->delete($this->form['denah_image']);
                }
            }
            $imagePath = $this->image_upload->store('denahs', 'public');
        }

        $this->form['denah_image'] = $imagePath;
        if ($this->isEdit) {
            $cluster = Cluster::find($this->editId);
            $cluster->update($this->form);
            session()->flash('success', 'Data Cluster & Konfigurasi berhasil diperbarui.');
        } else {
            Cluster::create($this->form);
            session()->flash('success', 'Cluster baru berhasil ditambahkan.');
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $cluster = Cluster::find($id);
        
        if ($cluster) {
            $this->deleteId = $id;
            $this->deleteName = $cluster->name;
            $this->showDeleteModal = true;
        }
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteName = null;
    }

    public function destroy()
    {
        if ($this->deleteId) {
            $cluster = Cluster::find($this->deleteId);
            if ($cluster) {
                if ($cluster->denah_image) {
                    Storage::disk('public')->delete($cluster->denah_image);
                }
                $cluster->delete();
                session()->flash('message', 'Cluster berhasil dihapus.');
            }
        }

        $this->closeDeleteModal();
    }

    public function closeModal()
    {
        $this->showFormModal = false;
        $this->image_upload = null; 
    }

    public function resetForm()
    {
        $this->form = [
            'name' => '',
            'type' => 'public',
            'description' => '',
            'is_active' => true,
            'delay_time' => 15,
            'min_duration_minutes' => 30,
            'max_duration_minutes' => 120,
            'denah_image' => null,
        ];
        $this->image_upload = null;
        $this->editId = null;
    }
}