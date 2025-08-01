<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use Livewire\WithPagination;


class ManageOrders extends Component
{
    use WithPagination;

    /**
     * The ID of the selected order.
     *
     * @var int|null
     */
    public $selectedOrderId;
    public $selectedOrder; // Define the selectedOrder variable
    public $search = ''; // Define the search property
    public $perPage = 10; // Default items per page
    public $perPageOptions = [5, 10, 25, 50]; // Options for items per page
    public $adminNotes = ''; // Property to store admin notes
    public $sortBy = 'created_at';
    public $sortDir = 'DESC';

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortBy' => ['except' => 'created_at'],
        'sortDir' => ['except' => 'DESC'],
    ];

    public function setSortBy($sortColumn)
    {
        if ($this->sortBy == $sortColumn) {
            $this->sortDir = ($this->sortDir == 'ASC') ? 'DESC' : 'ASC';
            return;
        }
        
        $this->sortBy = $sortColumn;
        $this->sortDir = 'ASC';
    }

    public function selectOrder($orderId)
    {
        $this->selectedOrderId = $orderId;
        $this->selectedOrder = Order::with(['user', 'order_items.product'])->find($orderId);
        
        if ($this->selectedOrder) {
            $this->adminNotes = $this->selectedOrder->admin_notes ?? '';
        } else {
            session()->flash('error', 'Commande non trouvée.');
            $this->selectedOrderId = null;
        }
    }

    public function approveOrder($orderId)
    {
        $order = Order::find($orderId);
        if ($order) {
            $order->status = 'approved';
            $order->admin_notes = $this->adminNotes;
            $order->approved_by = Auth::id();
            $order->approved_at = now();
            $order->save();
            session()->flash('message', 'Commande approuvée avec succès.');
            $this->closeModal();
        }
    }

    public function rejectOrder($orderId)
    {
        $order = Order::find($orderId);
        if ($order) {
            $order->status = 'rejected';
            $order->admin_notes = $this->adminNotes;
            $order->approved_by = Auth::id();
            $order->approved_at = now();
            $order->save();
            session()->flash('message', 'Commande rejetée avec succès.');
            $this->closeModal();
        }
    }

    public function hasInvalidUtf8($string)
    {
        return !mb_check_encoding($string, 'UTF-8');
    }

    public function addAdminNote($orderId, $note, $status = null)
    {
        try {
            $order = Order::find($orderId);
            if (!$order) {
                session()->flash('error', 'Commande non trouvée.');
                return;
            }

            // Always update the admin notes
            $order->admin_notes = $note;

            // If status is provided, update the order status
            if ($status !== null) {
                $order->status = $status;
                $order->approved_by = Auth::id(); // Set the admin who made the decision
                $order->approved_at = now(); // Set the timestamp of the decision
                
                // Add a status message
                $statusMessage = $status === 'approved' ? 'approuvée' : 'rejetée';
                session()->flash('message', "La commande a été $statusMessage avec succès.");
            } else {
                session()->flash('message', 'Note ajoutée avec succès.');
            }

            $order->save();

            // Reset the selected order
            $this->selectedOrderId = null;
            $this->adminNotes = '';

            // Log the action
            \Log::info('Admin note added to order', [
                'order_id' => $orderId, 
                'status' => $status ?? 'no status change', 
                'admin_id' => Auth::id(),
                'note' => $note
            ]);
        } catch (\Exception $e) {
            \Log::error('Error adding admin note', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Une erreur est survenue lors de l\'ajout de la note: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->selectedOrderId = null;
        $this->selectedOrder = null;
        $this->adminNotes = '';
    }

    public function addNoteOnly($orderId)
    {
        $order = Order::find($orderId);
        if ($order) {
            $order->admin_notes = $this->adminNotes;
            $order->save();
            session()->flash('message', 'Note ajoutée avec succès.');
            $this->closeModal();
        }
    }

    public function render()
    {
        $query = Order::query()
            ->select('orders.*')
            ->join('users', 'orders.user_id', '=', 'users.id');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('users.username', 'like', '%' . $this->search . '%')
                  ->orWhere('users.matricule', 'like', '%' . $this->search . '%')
                  ->orWhere('users.unite', 'like', '%' . $this->search . '%')
                  ->orWhere('users.administration', 'like', '%' . $this->search . '%')
                  ->orWhere('orders.id', 'like', '%' . $this->search . '%');
            });
        }

        switch ($this->sortBy) {
            case 'unite':
            case 'matricule':
            case 'username':
            case 'administration':
                $query->orderBy('users.' . $this->sortBy, $this->sortDir);
                break;
            case 'id':
                $query->orderBy('orders.id', $this->sortDir);
                break;
            default:
                $query->orderBy('orders.' . $this->sortBy, $this->sortDir);
        }

        $orders = $query->with(['user', 'order_items'])
            ->paginate($this->perPage);

        return view('livewire.manage-orders', [
            'orders' => $orders,
        ])->layout('layouts.admin-layout', [
            'title' => 'Gestion des Commandes'
        ]);
    }
}
