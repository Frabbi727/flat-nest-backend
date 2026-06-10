<?php

namespace App\Services;

use App\Contracts\Repositories\ListingAccessRequestRepositoryInterface;
use App\Enums\ListingAccessStatus;
use App\Enums\NotificationKind;
use App\Models\AppNotification;
use App\Models\Listing;
use App\Models\ListingAccessRequest;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ListingAccessService
{
    public function __construct(
        private readonly ListingAccessRequestRepositoryInterface $accessRequests,
        private readonly FcmService $fcm,
    ) {}

    public function requestAccess(Listing $listing, User $requester): ListingAccessRequest
    {
        if ($listing->owner_id === $requester->id) {
            throw new AccessDeniedHttpException('You cannot request access to your own listing.');
        }

        $existing = $this->accessRequests->findByListingAndRequester($listing->id, $requester->id);

        if ($existing && $existing->status === ListingAccessStatus::Accepted) {
            throw new UnprocessableEntityHttpException('You already have access to this listing.');
        }

        if ($existing && $existing->status === ListingAccessStatus::Pending) {
            throw new UnprocessableEntityHttpException('You already have a pending request for this listing.');
        }

        $accessRequest = $this->accessRequests->upsert($listing->id, $requester->id, $listing->owner_id);

        $title = 'New Contact Request';
        $body  = $requester->name . ' wants to see contact details for "' . $listing->title . '"';

        AppNotification::create([
            'user_id'      => $listing->owner_id,
            'kind'         => NotificationKind::ContactInfoRequested->value,
            'title'        => $title,
            'body'         => $body,
            'reference_id' => $accessRequest->id,
        ]);
        $this->fcm->sendToUser($listing->owner_id, $title, $body, [
            'kind'         => NotificationKind::ContactInfoRequested->value,
            'reference_id' => $accessRequest->id,
        ]);

        return $accessRequest;
    }

    public function grantAccess(string $requestId, User $owner): void
    {
        $accessRequest = $this->accessRequests->findById($requestId);

        if (! $accessRequest) {
            throw new NotFoundHttpException('Access request not found.');
        }

        if ($accessRequest->listing->owner_id !== $owner->id) {
            throw new AccessDeniedHttpException('You do not own this listing.');
        }

        if ($accessRequest->status !== ListingAccessStatus::Pending) {
            throw new UnprocessableEntityHttpException('This request has already been responded to.');
        }

        $this->accessRequests->updateStatus($requestId, ListingAccessStatus::Accepted);

        $title = 'Request Accepted';
        $body  = 'Owner shared contact details for "' . $accessRequest->listing->title . '"';

        AppNotification::create([
            'user_id'      => $accessRequest->requester_id,
            'kind'         => NotificationKind::ContactInfoGranted->value,
            'title'        => $title,
            'body'         => $body,
            'reference_id' => $accessRequest->listing_id,
        ]);
        $this->fcm->sendToUser($accessRequest->requester_id, $title, $body, [
            'kind'         => NotificationKind::ContactInfoGranted->value,
            'reference_id' => $accessRequest->listing_id,
        ]);
    }

    public function denyAccess(string $requestId, User $owner): void
    {
        $accessRequest = $this->accessRequests->findById($requestId);

        if (! $accessRequest) {
            throw new NotFoundHttpException('Access request not found.');
        }

        if ($accessRequest->listing->owner_id !== $owner->id) {
            throw new AccessDeniedHttpException('You do not own this listing.');
        }

        if ($accessRequest->status !== ListingAccessStatus::Pending) {
            throw new UnprocessableEntityHttpException('This request has already been responded to.');
        }

        $this->accessRequests->updateStatus($requestId, ListingAccessStatus::Rejected);

        $title = 'Request Declined';
        $body  = 'Owner declined your request for "' . $accessRequest->listing->title . '"';

        AppNotification::create([
            'user_id'      => $accessRequest->requester_id,
            'kind'         => NotificationKind::ContactInfoDenied->value,
            'title'        => $title,
            'body'         => $body,
            'reference_id' => $accessRequest->listing_id,
        ]);
        $this->fcm->sendToUser($accessRequest->requester_id, $title, $body, [
            'kind'         => NotificationKind::ContactInfoDenied->value,
            'reference_id' => $accessRequest->listing_id,
        ]);
    }

    public function getOwnerRequests(string $ownerId, ?string $status, int $perPage = 15): LengthAwarePaginator
    {
        return $this->accessRequests->findForOwner($ownerId, $status, $perPage);
    }
}
