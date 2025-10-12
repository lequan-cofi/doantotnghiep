<?php

namespace App\Observers;

use App\Models\Viewing;
use App\Services\CommissionEventService;
use Illuminate\Support\Facades\Log;

class ViewingObserver
{
    protected $commissionEventService;

    public function __construct(CommissionEventService $commissionEventService)
    {
        $this->commissionEventService = $commissionEventService;
    }

    /**
     * Handle the Viewing "created" event.
     */
    public function created(Viewing $viewing): void
    {
        // Không tạo sự kiện hoa hồng khi tạo viewing, chỉ khi hoàn thành
        Log::info('Viewing created', [
            'viewing_id' => $viewing->id,
            'agent_id' => $viewing->agent_id,
            'status' => $viewing->status
        ]);
    }

    /**
     * Handle the Viewing "updated" event.
     */
    public function updated(Viewing $viewing): void
    {
        // Tạo sự kiện hoa hồng khi viewing được hoàn thành (status = 'done')
        if ($viewing->isDirty('status') && $viewing->status === 'done') {
            $this->createCommissionEvents($viewing);
        }
    }

    /**
     * Handle the Viewing "deleted" event.
     */
    public function deleted(Viewing $viewing): void
    {
        Log::info('Viewing deleted', [
            'viewing_id' => $viewing->id,
            'agent_id' => $viewing->agent_id
        ]);
    }

    /**
     * Create commission events for viewing when completed
     */
    private function createCommissionEvents(Viewing $viewing)
    {
        try {
            $result = $this->commissionEventService->createCommissionEventsForViewing($viewing);
            
            if ($result) {
                Log::info('Commission events created successfully via ViewingObserver', [
                    'viewing_id' => $viewing->id,
                    'created_events_count' => count($result)
                ]);
            } else {
                Log::warning('Failed to create commission events via ViewingObserver', [
                    'viewing_id' => $viewing->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error creating commission events in ViewingObserver: ' . $e->getMessage(), [
                'viewing_id' => $viewing->id,
                'error' => $e->getTraceAsString()
            ]);
        }
    }
}
