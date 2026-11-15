@extends('layouts.master')
@section('content')
@php
    $clientTypes = [
        1 => 'Individual',
        2 => 'Company',
        3 => 'Government',
        4 => 'Organization',
    ];
    $clientStatuses = [
        '1' => 'Active',
        '2' => 'Inactive',
        '3' => 'Pending',
        '4' => 'Blocked',
    ];
    $statusColors = [
        '1' => 'success',
        '2' => 'secondary',
        '3' => 'warning',
        '4' => 'danger',
    ];
    $clientType = $clientTypes[$client->client_type] ?? 'Unknown';
    $clientStatus = $clientStatuses[$client->status] ?? 'Unknown';
    $statusColor = $statusColors[$client->status] ?? 'secondary';
@endphp

<style>
    .client-view-page {
        padding: 10px 5px 25px;
    }
    .client-page-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 25px;
        padding: 0 5px 20px;
        border-bottom: 1px solid #eee;
    }
    .client-page-heading h3 {
        font-size: 25px;
        font-weight: 700;
        color: #222;
        margin-bottom: 7px;
    }
    .client-page-heading p {
        color: #777;
        margin-bottom: 0;
        font-size: 14px;
    }
    .client-edit-btn {
        background: #7138e8;
        color: #fff !important;
        padding: 11px 22px;
        border-radius: 7px;
        text-decoration: none !important;
        font-size: 13px;
        box-shadow: 0 4px 10px rgba(113, 56, 232, .2);
    }
    .client-edit-btn:hover {
        background: #5d28ce;
    }
    .client-profile-card,
    .client-info-card,
    .client-overview-card {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 10px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .05);
    }
    .client-profile-card {
        padding: 30px 20px;
        text-align: center;
        height: 100%;
    }
    .client-avatar {
        width: 135px;
        height: 135px;
        margin: 0 auto 20px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e9dcff, #c5a7ff);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #7138e8;
        font-size: 48px;
        font-weight: 700;
        border: 5px solid #fff;
        box-shadow: 0 0 0 4px #eee5ff;
    }
    .client-profile-card h4 {
        font-size: 21px;
        font-weight: 600;
        color: #222;
        margin-bottom: 7px;
        word-break: break-word;
    }
    .client-company-name {
        color: #7138e8;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 15px;
    }
    .client-status {
        display: inline-block;
        padding: 6px 17px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 22px;
    }
    .client-meta {
        border-top: 1px solid #eee;
        padding-top: 20px;
        text-align: left;
    }
    .client-meta-item {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 17px;
        font-size: 13px;
    }
    .client-meta-item span:first-child {
        color: #777;
    }
    .client-meta-item span:last-child {
        color: #222;
        font-weight: 600;
        text-align: right;
        word-break: break-word;
    }
    .client-info-card {
        padding: 22px 25px;
        margin-bottom: 20px;
    }
    .client-card-title {
        font-size: 17px;
        font-weight: 700;
        color: #222;
        margin-bottom: 20px;
    }
    .client-info-row {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
        gap: 15px;
    }
    .client-info-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .client-info-icon {
        width: 38px;
        height: 38px;
        min-width: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f0e8ff;
        color: #7138e8;
        border-radius: 7px;
        font-size: 17px;
    }
    .client-info-label {
        color: #555;
        font-size: 13px;
    }
    .client-info-value {
        margin-left: auto;
        text-align: right;
        color: #222;
        font-size: 13px;
        font-weight: 600;
        word-break: break-word;
        max-width: 55%;
    }
    .client-overview-card {
        padding: 22px 20px;
    }
    .client-overview-item {
        text-align: center;
        padding: 10px 5px;
        border-right: 1px solid #eee;
    }
    .client-overview-item:last-child {
        border-right: none;
    }
    .client-overview-icon {
        width: 38px;
        height: 38px;
        margin: 0 auto 10px;
        border-radius: 8px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 17px;
        color: #fff;
    }
    .client-overview-item h5 {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 7px;
        color: #555;
    }
    .client-overview-item h3 {
        font-size: 23px;
        font-weight: 700;
        color: #222;
        margin-bottom: 0;
    }
    .client-notes {
        background: #f8f5ff;
        border-left: 3px solid #7138e8;
        border-radius: 5px;
        padding: 15px;
        margin-top: 20px;
        text-align: left;
    }
    .client-notes h6 {
        font-weight: 700;
        font-size: 13px;
        margin-bottom: 8px;
    }
    .client-notes p {
        font-size: 13px;
        color: #666;
        margin-bottom: 0;
        line-height: 1.7;
    }
    @media(max-width: 767px) {
        .client-info-card {
            padding: 18px 15px;
        }
        .client-info-row {
            flex-wrap: wrap;
        }
        .client-info-value {
            max-width: 60%;
        }
        .client-overview-item {
            border-right: none;
            border-bottom: 1px solid #eee;
            margin-bottom: 10px;
        }
    }

    .client-detail-tabs {
        border-bottom: 1px solid #eee;
        gap: 5px;
        flex-wrap: wrap;
    }
    .client-detail-tabs .nav-link {
        color: #777;
        font-size: 13px;
        font-weight: 600;
        padding: 13px 16px;
        border: none;
        border-bottom: 3px solid transparent;
        border-radius: 0;
    }
    .client-detail-tabs .nav-link:hover {
        color: #7138e8;
        background: #faf8ff;
    }
    .client-detail-tabs .nav-link.active {
        color: #7138e8;
        background: transparent;
        border: none;
        border-bottom: 3px solid #7138e8;
    }
    .client-tab-content {
        padding-top: 25px;
        min-height: 400px;
    }
    .client-tab-content .tab-pane {
        animation: clientTabFade .25s ease-in-out;
    }
    @keyframes clientTabFade {
        from {
            opacity: 0;
            transform: translateY(5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
<div class="content-wrapper">
    <div class="client-view-page">
        <!-- Page Heading -->
        <div class="client-page-heading">
            <div>
                <h3>Client Profile</h3>
                <p>View and manage client information and details.</p>
            </div>
            <div class="d-flex" style="gap: 10px;">
                <a href="{{ route('clients.index') }}"
                class="btn btn-light">
                    <i class="ti-arrow-left"></i> Back
                </a>
                <a href="{{ route('clients.edit', $client->id) }}"
                class="btn btn-light">
                    <i class="ti-pencil"></i> Edit Client
                </a>
            </div>
        </div>
        <div class="row">
            <!-- Left: Client Profile -->
            <div class="col-lg-4 col-md-5 mb-4">
                <div class="client-profile-card">
                    <div class="client-avatar">
                        {{ strtoupper(mb_substr($client->name, 0, 1)) }}
                    </div>
                    <h4>{{ $client->name }}</h4>
                    <div class="client-company-name">
                        {{ $client->company_name ?? $clientType }}
                    </div>
                    <span class="client-status badge badge-{{ $statusColor }}">
                        {{ $clientStatus }}
                    </span>
                    <div class="client-meta">
                        <div class="client-meta-item">
                            <span>
                                <i class="ti-id-badge mr-2"></i> Client Code
                            </span>
                            <span>{{ $client->client_code }}</span>
                        </div>
                        <div class="client-meta-item">
                            <span>
                                <i class="ti-calendar mr-2"></i> Member Since
                            </span>
                            <span>{{ $client->created_at?->format('d M, Y') ?? 'N/A' }}</span>
                        </div>
                        <div class="client-meta-item">
                            <span>
                                <i class="ti-time mr-2"></i> Last Updated
                            </span>
                            <span>{{ $client->updated_at?->format('d M, Y') ?? 'N/A' }}</span>
                        </div>
                    </div>
                    @if($client->notes)
                        <div class="client-notes">
                            <h6>
                                <i class="ti-notepad mr-2"></i> Client Notes
                            </h6>
                            <p>{{ $client->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
            <!-- Right Side: Client Details Tabs -->
            <div class="col-lg-8 col-md-7">
                <div class="client-info-card">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs client-detail-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active"
                            data-toggle="tab"
                            href="#client-information"
                            role="tab">
                                <i class="ti-user mr-2"></i>
                                Client Information
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link"
                            data-toggle="tab"
                            href="#additional-information"
                            role="tab">
                                <i class="ti-info-alt mr-2"></i>
                                Additional Information
                            </a>
                        </li>
                    </ul>
                    <!-- Tab Content -->
                    <div class="tab-content client-tab-content">
                        <!-- Tab 1: Client Information -->
                        <div class="tab-pane fade show active"
                            id="client-information"
                            role="tabpanel">
                            <h5 class="client-card-title">
                                <i class="ti-user mr-2"></i>
                                Personal & Business Information
                            </h5>
                            <div class="client-info-row">
                                <div class="client-info-icon">
                                    <i class="ti-user"></i>
                                </div>
                                <div class="client-info-label">Full Name</div>
                                <div class="client-info-value">
                                    {{ $client->name }}
                                </div>
                            </div>
                            <div class="client-info-row">
                                <div class="client-info-icon">
                                    <i class="ti-briefcase"></i>
                                </div>
                                <div class="client-info-label">Client Type</div>
                                <div class="client-info-value">
                                    {{ $clientType }}
                                </div>
                            </div>
                            <div class="client-info-row">
                                <div class="client-info-icon">
                                    <i class="ti-home"></i>
                                </div>
                                <div class="client-info-label">Company Name</div>
                                <div class="client-info-value">
                                    {{ $client->company_name ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="client-info-row">
                                <div class="client-info-icon">
                                    <i class="ti-email"></i>
                                </div>
                                <div class="client-info-label">Email Address</div>
                                <div class="client-info-value">
                                    {{ $client->email ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="client-info-row">
                                <div class="client-info-icon">
                                    <i class="ti-mobile"></i>
                                </div>
                                <div class="client-info-label">Phone Number</div>
                                <div class="client-info-value">
                                    {{ $client->phone ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="client-info-row">
                                <div class="client-info-icon">
                                    <i class="ti-mobile"></i>
                                </div>
                                <div class="client-info-label">Alternate Phone</div>
                                <div class="client-info-value">
                                    {{ $client->alternate_phone ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="client-info-row">
                                <div class="client-info-icon">
                                    <i class="ti-world"></i>
                                </div>
                                <div class="client-info-label">Website</div>
                                <div class="client-info-value">
                                    @if($client->website)
                                        <a href="{{ \Illuminate\Support\Str::startsWith($client->website, ['http://', 'https://']) ? $client->website : 'https://' . $client->website }}"
                                        target="_blank"
                                        rel="noopener noreferrer">
                                            {{ $client->website }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                            <div class="client-info-row">
                                <div class="client-info-icon">
                                    <i class="ti-receipt"></i>
                                </div>
                                <div class="client-info-label">Tax Number</div>
                                <div class="client-info-value">
                                    {{ $client->tax_number ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                        <!-- Tab 2: Additional Information -->
                        <div class="tab-pane fade"
                            id="additional-information"
                            role="tabpanel">
                            <h5 class="client-card-title">
                                <i class="ti-info-alt mr-2"></i>
                                Address & Additional Details
                            </h5>
                            <div class="client-info-row">
                                <div class="client-info-icon">
                                    <i class="ti-map-alt"></i>
                                </div>
                                <div class="client-info-label">Full Address</div>
                                <div class="client-info-value">
                                    {{ $client->address ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="client-info-row">
                                <div class="client-info-icon">
                                    <i class="ti-map"></i>
                                </div>
                                <div class="client-info-label">City</div>
                                <div class="client-info-value">
                                    {{ $client->city ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="client-info-row">
                                <div class="client-info-icon">
                                    <i class="ti-location-arrow"></i>
                                </div>
                                <div class="client-info-label">State / Division</div>
                                <div class="client-info-value">
                                    {{ $client->state ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="client-info-row">
                                <div class="client-info-icon">
                                    <i class="ti-flag"></i>
                                </div>
                                <div class="client-info-label">Country</div>
                                <div class="client-info-value">
                                    {{ $client->country ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="client-info-row">
                                <div class="client-info-icon">
                                    <i class="ti-pin"></i>
                                </div>
                                <div class="client-info-label">Postal Code</div>
                                <div class="client-info-value">
                                    {{ $client->postal_code ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="client-info-row">
                                <div class="client-info-icon">
                                    <i class="ti-notepad"></i>
                                </div>
                                <div class="client-info-label">Notes</div>
                                <div class="client-info-value">
                                    {{ $client->notes ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
