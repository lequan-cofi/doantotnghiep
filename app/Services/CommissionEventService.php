<?php

namespace App\Services;

use App\Models\CommissionEvent;
use App\Models\CommissionPolicy;
use App\Models\Lease;
use App\Models\BookingDeposit;
use App\Models\Viewing;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CommissionEventService
{
    /**
     * Tạo sự kiện hoa hồng cho hợp đồng thuê
     */
    public function createCommissionEventsForLease(Lease $lease)
    {
        try {
            DB::beginTransaction();

            $organization = $lease->organization;
            if (!$organization) {
                Log::warning('No organization found for lease, skipping commission events', [
                    'lease_id' => $lease->id
                ]);
                DB::rollBack();
                return false;
            }

            $createdEvents = [];

            // 1. Tạo sự kiện hoa hồng ký hợp đồng (lease_signed)
            $leaseEvents = $this->createLeaseSignedEvents($lease, $organization);
            $createdEvents = array_merge($createdEvents, $leaseEvents);

            // 2. Tạo sự kiện hoa hồng tiền cọc (deposit_paid) nếu có tiền cọc
            if ($lease->deposit_amount > 0) {
                $depositEvents = $this->createDepositPaidEvents($lease, $organization);
                $createdEvents = array_merge($createdEvents, $depositEvents);
            }

            DB::commit();

            Log::info('Commission events created successfully for lease', [
                'lease_id' => $lease->id,
                'organization_id' => $organization->id,
                'created_events_count' => count($createdEvents),
                'events' => $createdEvents
            ]);

            return $createdEvents;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating commission events for lease: ' . $e->getMessage(), [
                'lease_id' => $lease->id,
                'error' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Tạo sự kiện hoa hồng ký hợp đồng
     */
    private function createLeaseSignedEvents(Lease $lease, $organization)
    {
        $events = [];

        // Lấy các chính sách hoa hồng cho trigger 'lease_signed'
        $leasePolicies = CommissionPolicy::where('organization_id', $organization->id)
            ->where('trigger_event', 'lease_signed')
            ->where('active', true)
            ->get();

        Log::info('Found ' . $leasePolicies->count() . ' lease_signed commission policies for organization ' . $organization->id);

        foreach ($leasePolicies as $policy) {
            $baseAmount = $lease->rent_amount;
            $commissionTotal = $this->calculateCommission($policy, $baseAmount);

            if ($commissionTotal > 0) {
                $event = CommissionEvent::create([
                    'policy_id' => $policy->id,
                    'organization_id' => $organization->id,
                    'trigger_event' => 'lease_signed',
                    'ref_type' => 'lease',
                    'ref_id' => $lease->id,
                    'lease_id' => $lease->id,
                    'unit_id' => $lease->unit_id,
                    'agent_id' => $lease->agent_id,
                    'user_id' => $lease->agent_id,
                    'occurred_at' => $lease->signed_at ?? now(),
                    'amount_base' => $baseAmount,
                    'commission_total' => $commissionTotal,
                    'status' => 'pending'
                ]);

                $events[] = [
                    'id' => $event->id,
                    'type' => 'lease_signed',
                    'amount' => $commissionTotal,
                    'policy_id' => $policy->id
                ];

                Log::info('Created lease_signed commission event', [
                    'event_id' => $event->id,
                    'lease_id' => $lease->id,
                    'policy_id' => $policy->id,
                    'amount' => $commissionTotal
                ]);
            }
        }

        return $events;
    }

    /**
     * Tạo sự kiện hoa hồng tiền cọc
     */
    private function createDepositPaidEvents(Lease $lease, $organization)
    {
        $events = [];

        // Lấy các chính sách hoa hồng cho trigger 'deposit_paid'
        $depositPolicies = CommissionPolicy::where('organization_id', $organization->id)
            ->where('trigger_event', 'deposit_paid')
            ->where('active', true)
            ->get();

        Log::info('Found ' . $depositPolicies->count() . ' deposit_paid commission policies for organization ' . $organization->id);

        foreach ($depositPolicies as $policy) {
            $baseAmount = $lease->deposit_amount;
            $commissionTotal = $this->calculateCommission($policy, $baseAmount);

            if ($commissionTotal > 0) {
                $event = CommissionEvent::create([
                    'policy_id' => $policy->id,
                    'organization_id' => $organization->id,
                    'trigger_event' => 'deposit_paid',
                    'ref_type' => 'lease',
                    'ref_id' => $lease->id,
                    'lease_id' => $lease->id,
                    'unit_id' => $lease->unit_id,
                    'agent_id' => $lease->agent_id,
                    'user_id' => $lease->agent_id,
                    'occurred_at' => $lease->signed_at ?? now(),
                    'amount_base' => $baseAmount,
                    'commission_total' => $commissionTotal,
                    'status' => 'pending'
                ]);

                $events[] = [
                    'id' => $event->id,
                    'type' => 'deposit_paid',
                    'amount' => $commissionTotal,
                    'policy_id' => $policy->id
                ];

                Log::info('Created deposit_paid commission event', [
                    'event_id' => $event->id,
                    'lease_id' => $lease->id,
                    'policy_id' => $policy->id,
                    'amount' => $commissionTotal
                ]);
            }
        }

        return $events;
    }

    /**
     * Tạo sự kiện hoa hồng cho viewing khi được hoàn thành
     */
    public function createCommissionEventsForViewing(Viewing $viewing)
    {
        try {
            DB::beginTransaction();

            $organization = $viewing->organization;
            if (!$organization) {
                Log::warning('No organization found for viewing, skipping commission events', [
                    'viewing_id' => $viewing->id
                ]);
                DB::rollBack();
                return false;
            }

            // Chỉ tạo sự kiện hoa hồng khi viewing được hoàn thành
            if ($viewing->status !== 'done') {
                Log::info('Viewing not completed yet, skipping commission events', [
                    'viewing_id' => $viewing->id,
                    'status' => $viewing->status
                ]);
                DB::rollBack();
                return false;
            }

            $events = [];

            // Lấy các chính sách hoa hồng cho trigger 'viewing_done'
            $viewingPolicies = CommissionPolicy::where('organization_id', $organization->id)
                ->where('trigger_event', 'viewing_done')
                ->where('active', true)
                ->get();

            Log::info('Found ' . $viewingPolicies->count() . ' viewing_done commission policies for viewing organization ' . $organization->id);

            foreach ($viewingPolicies as $policy) {
                // Đối với viewing, base amount thường là flat amount hoặc có thể dựa trên giá thuê của property
                $baseAmount = $this->getViewingBaseAmount($viewing, $policy);
                $commissionTotal = $this->calculateCommission($policy, $baseAmount);

                if ($commissionTotal > 0) {
                    $event = CommissionEvent::create([
                        'policy_id' => $policy->id,
                        'organization_id' => $organization->id,
                        'trigger_event' => 'viewing_done',
                        'ref_type' => 'viewing',
                        'ref_id' => $viewing->id,
                        'lease_id' => null, // Viewing chưa có lease
                        'unit_id' => $viewing->unit_id,
                        'agent_id' => $viewing->agent_id,
                        'user_id' => $viewing->agent_id,
                        'occurred_at' => $viewing->updated_at ?? now(),
                        'amount_base' => $baseAmount,
                        'commission_total' => $commissionTotal,
                        'status' => 'pending'
                    ]);

                    $events[] = [
                        'id' => $event->id,
                        'type' => 'viewing_done',
                        'amount' => $commissionTotal,
                        'policy_id' => $policy->id
                    ];

                    Log::info('Created viewing_done commission event', [
                        'event_id' => $event->id,
                        'viewing_id' => $viewing->id,
                        'policy_id' => $policy->id,
                        'amount' => $commissionTotal
                    ]);
                }
            }

            DB::commit();

            Log::info('Commission events created successfully for viewing', [
                'viewing_id' => $viewing->id,
                'organization_id' => $organization->id,
                'created_events_count' => count($events),
                'events' => $events
            ]);

            return $events;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating commission events for viewing: ' . $e->getMessage(), [
                'viewing_id' => $viewing->id,
                'error' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Tạo sự kiện hoa hồng cho booking deposit khi được thanh toán
     */
    public function createCommissionEventsForBookingDeposit(BookingDeposit $bookingDeposit)
    {
        try {
            DB::beginTransaction();

            $organization = $bookingDeposit->organization;
            if (!$organization) {
                Log::warning('No organization found for booking deposit, skipping commission events', [
                    'booking_deposit_id' => $bookingDeposit->id
                ]);
                DB::rollBack();
                return false;
            }

            // Chỉ tạo sự kiện hoa hồng khi deposit được thanh toán
            if ($bookingDeposit->payment_status !== 'paid') {
                Log::info('Booking deposit not paid yet, skipping commission events', [
                    'booking_deposit_id' => $bookingDeposit->id,
                    'payment_status' => $bookingDeposit->payment_status
                ]);
                DB::rollBack();
                return false;
            }

            $events = [];

            // Lấy các chính sách hoa hồng cho trigger 'deposit_paid'
            $depositPolicies = CommissionPolicy::where('organization_id', $organization->id)
                ->where('trigger_event', 'deposit_paid')
                ->where('active', true)
                ->get();

            Log::info('Found ' . $depositPolicies->count() . ' deposit_paid commission policies for booking deposit organization ' . $organization->id);

            foreach ($depositPolicies as $policy) {
                $baseAmount = $bookingDeposit->amount;
                $commissionTotal = $this->calculateCommission($policy, $baseAmount);

                if ($commissionTotal > 0) {
                    $event = CommissionEvent::create([
                        'policy_id' => $policy->id,
                        'organization_id' => $organization->id,
                        'trigger_event' => 'deposit_paid',
                        'ref_type' => 'booking_deposit',
                        'ref_id' => $bookingDeposit->id,
                        'lease_id' => $bookingDeposit->lead ? $bookingDeposit->lead->lease_id : null,
                        'unit_id' => $bookingDeposit->unit_id,
                        'agent_id' => $bookingDeposit->agent_id,
                        'user_id' => $bookingDeposit->agent_id,
                        'occurred_at' => $bookingDeposit->paid_at ?? now(),
                        'amount_base' => $baseAmount,
                        'commission_total' => $commissionTotal,
                        'status' => 'pending'
                    ]);

                    $events[] = [
                        'id' => $event->id,
                        'type' => 'deposit_paid',
                        'amount' => $commissionTotal,
                        'policy_id' => $policy->id
                    ];

                    Log::info('Created deposit_paid commission event for booking deposit', [
                        'event_id' => $event->id,
                        'booking_deposit_id' => $bookingDeposit->id,
                        'policy_id' => $policy->id,
                        'amount' => $commissionTotal
                    ]);
                }
            }

            DB::commit();

            Log::info('Commission events created successfully for booking deposit', [
                'booking_deposit_id' => $bookingDeposit->id,
                'organization_id' => $organization->id,
                'created_events_count' => count($events),
                'events' => $events
            ]);

            return $events;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating commission events for booking deposit: ' . $e->getMessage(), [
                'booking_deposit_id' => $bookingDeposit->id,
                'error' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Tính toán hoa hồng dựa trên chính sách
     */
    private function calculateCommission($policy, $baseAmount)
    {
        $commission = 0;

        switch ($policy->calc_type) {
            case 'percent':
                $commission = $baseAmount * ($policy->percent_value / 100);
                break;
            case 'flat':
                $commission = $policy->flat_amount;
                break;
            case 'tiered':
                // Implement tiered calculation logic here if needed
                $commission = 0;
                break;
            default:
                $commission = 0;
        }

        // Áp dụng min và cap
        if ($policy->min_amount && $commission < $policy->min_amount) {
            $commission = $policy->min_amount;
        }
        if ($policy->cap_amount && $commission > $policy->cap_amount) {
            $commission = $policy->cap_amount;
        }

        return $commission;
    }

    /**
     * Cập nhật sự kiện hoa hồng khi hợp đồng thay đổi
     */
    public function updateCommissionEventsForLease(Lease $lease)
    {
        try {
            // Tìm các sự kiện hoa hồng liên quan đến hợp đồng này
            $events = CommissionEvent::where('lease_id', $lease->id)
                ->where('status', 'pending')
                ->get();

            foreach ($events as $event) {
                $policy = $event->policy;
                if (!$policy) {
                    continue;
                }

                // Tính lại hoa hồng dựa trên trigger event
                $baseAmount = 0;
                if ($event->trigger_event === 'lease_signed') {
                    $baseAmount = $lease->rent_amount;
                } elseif ($event->trigger_event === 'deposit_paid') {
                    $baseAmount = $lease->deposit_amount;
                }

                if ($baseAmount > 0) {
                    $newCommission = $this->calculateCommission($policy, $baseAmount);
                    
                    $event->update([
                        'amount_base' => $baseAmount,
                        'commission_total' => $newCommission,
                    ]);

                    Log::info('Updated commission event', [
                        'event_id' => $event->id,
                        'old_amount' => $event->getOriginal('commission_total'),
                        'new_amount' => $newCommission
                    ]);
                }
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Error updating commission events for lease: ' . $e->getMessage(), [
                'lease_id' => $lease->id,
                'error' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Xóa sự kiện hoa hồng khi hợp đồng bị xóa
     */
    public function deleteCommissionEventsForLease(Lease $lease)
    {
        try {
            $events = CommissionEvent::where('lease_id', $lease->id)
                ->where('status', 'pending')
                ->get();

            foreach ($events as $event) {
                $event->delete();
            }

            Log::info('Deleted commission events for lease', [
                'lease_id' => $lease->id,
                'deleted_count' => $events->count()
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error deleting commission events for lease: ' . $e->getMessage(), [
                'lease_id' => $lease->id,
                'error' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Lấy base amount cho viewing commission
     */
    private function getViewingBaseAmount(Viewing $viewing, $policy)
    {
        // Đối với viewing, base amount có thể là:
        // 1. Flat amount từ policy (nếu calc_type = 'flat')
        // 2. Giá thuê của property (nếu calc_type = 'percent')
        // 3. Một giá trị cố định
        
        if ($policy->calc_type === 'flat') {
            return $policy->flat_amount ?? 0;
        }
        
        // Nếu là percent, có thể dựa trên giá thuê của property
        if ($viewing->property) {
            // Ưu tiên sử dụng property_id trực tiếp
            return $viewing->property->price ?? 1000000; // Default 1M VND
        } elseif ($viewing->unit && $viewing->unit->property) {
            // Fallback sử dụng property từ unit
            return $viewing->unit->property->price ?? 1000000; // Default 1M VND
        }
        
        // Default base amount
        return 1000000; // 1M VND
    }
}
