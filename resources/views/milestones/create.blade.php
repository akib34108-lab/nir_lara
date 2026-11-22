
@extends('layouts.master')

@section('content')

<div class="content-wrapper">

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">

            <div class="card">
                <div class="card-body">

                    <div class="row">

                        <div class="col-sm-8">
                            <h4 class="card-title">Project Milestone Setup</h4>

                            <p class="card-description">
                                Setup milestones for {{ $project->project_name }}
                            </p>
                        </div>

                        <div class="col-sm-4 d-flex justify-content-end align-items-center">

                            @can('milestones-create')
                                <button type="button"
                                    class="btn btn-primary btn-fw"
                                    id="addMilestoneBtn">

                                    <i class="ti-plus"></i> Add Milestone

                                </button>
                            @endcan

                        </div>

                    </div>

                    <hr>

                    <form class="forms-sample"
                        method="POST"
                        id="milestoneForm"
                        action="{{ route('projects.milestones.store', $project->id) }}">

                        @csrf

                        {{-- Project Information --}}
                        <div class="form-group row">

                            <div class="col-sm-6">
                                <label class="col-form-label">Project Code</label>

                                <input type="text"
                                    class="form-control"
                                    value="{{ $project->project_code }}"
                                    readonly>
                            </div>

                            <div class="col-sm-6">
                                <label class="col-form-label">Project Name</label>

                                <input type="text"
                                    class="form-control"
                                    value="{{ $project->project_name }}"
                                    readonly>
                            </div>

                        </div>

                        <hr>

                        {{-- Milestone Information --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <h4 class="card-title mb-0">
                                Milestone Information
                            </h4>

                            <button type="button"
                                id="addMilestone"
                                class="btn btn-success btn-sm">

                                <i class="ti-plus"></i> Add More Milestone

                            </button>

                        </div>

                        @error('milestones')
                            <div class="alert alert-danger">
                                {{ $message }}
                            </div>
                        @enderror

                        <div id="milestoneContainer">

                            @php
                                $oldMilestones = old('milestones', [
                                    [
                                        'milestone_name' => '',
                                        'percentage' => ''
                                    ]
                                ]);
                            @endphp

                            @foreach($oldMilestones as $index => $milestone)

                                <div class="milestone-item border rounded p-3 mb-3">

                                    <div class="d-flex justify-content-between mb-3">

                                        <h5 class="milestone-title">
                                            Milestone {{ $index + 1 }}
                                        </h5>

                                        @if($index > 0)
                                            <button type="button"
                                                class="btn btn-danger btn-sm removeMilestone">

                                                <i class="ti-trash"></i> Remove

                                            </button>
                                        @endif

                                    </div>

                                    <div class="form-group row">

                                        <div class="col-sm-4">

                                            <label class="col-form-label">
                                                Milestone Name
                                            </label>

                                            <select name="milestones[{{ $index }}][milestone_name]"
                                                class="form-control milestone-name-select"
                                                required>

                                                <option value="">
                                                    Select Milestone
                                                </option>

                                                @foreach($milestones as $item)

                                                    <option value="{{ $item->milestone_name }}"
                                                        {{ ($milestone['milestone_name'] ?? '') == $item->milestone_name ? 'selected' : '' }}>

                                                        {{ $item->milestone_name }}

                                                    </option>

                                                @endforeach

                                            </select>

                                            @error("milestones.$index.milestone_name")
                                                <small class="text-danger">
                                                    {{ $message }}
                                                </small>
                                            @enderror

                                        </div>

                                        <div class="col-sm-4">

                                            <label class="col-form-label">
                                                Percentage (%)
                                            </label>

                                            <input type="number"
                                                name="milestones[{{ $index }}][percentage]"
                                                class="form-control percentage"
                                                value="{{ $milestone['percentage'] ?? '' }}"
                                                min="0.01"
                                                max="100"
                                                step="0.01"
                                                placeholder="Enter percentage"
                                                required>

                                            @error("milestones.$index.percentage")
                                                <small class="text-danger">
                                                    {{ $message }}
                                                </small>
                                            @enderror

                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                        {{-- Total Percentage --}}
                        <div class="form-group row">

                            <div class="col-sm-6">

                                <label class="col-form-label">
                                    Total Percentage
                                </label>

                                <input type="text"
                                    id="totalPercentage"
                                    class="form-control"
                                    value="0%"
                                    readonly>

                                <small id="percentageMessage"></small>

                            </div>

                        </div>

                        <hr>

                        <button type="submit"
                            class="btn btn-primary mr-2">

                            <i class="ti-save"></i> Save Milestones

                        </button>

                        <a href="{{ route('projects.index') }}"
                            class="btn btn-light">

                            <i class="ti-arrow-left"></i> Cancel

                        </a>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>


{{-- Popover Content --}}
<div id="milestonePopoverContent" style="display: none;">

    <div style="min-width: 220px;">

        <input type="text"
            class="form-control new-milestone-name"
            placeholder="Enter milestone name">

        <button type="button"
            class="btn btn-primary btn-sm mt-2 saveNewMilestone">

            Add

        </button>

        {{-- Success Message --}}
        <div class="milestone-success-message text-success mt-2"
            style="display: none; font-size: 13px;">

            <i class="ti-check"></i>
            Milestone added successfully!

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>

$(document).ready(function () {

    let milestoneIndex = {{ count($oldMilestones) }};


    // ========================================
    // Initialize Bootstrap Popover
    // ========================================

    if (typeof $.fn.popover !== 'function') {

        console.error('Bootstrap Popover is not loaded.');

    } else {

        $('#addMilestoneBtn').popover({

            html: true,
            sanitize: false,
            trigger: 'click',
            placement: 'bottom',
            container: 'body',
            animation: true,

            content: function () {

                return $('#milestonePopoverContent').html();

            }

        });

        // Close Popover when clicking outside
        $(document).on('click', function (event) {

            if (
                $(event.target).closest('#addMilestoneBtn, .popover').length === 0
            ) {

                $('#addMilestoneBtn').popover('hide');

            }

        });

    }


    // ========================================
    // Add New Milestone from Popover
    // ========================================

    $(document).on('click', '.saveNewMilestone', function () {

        let button = $(this);

        let popover = button.closest('.popover');

        let milestoneName = popover.find('.new-milestone-name')
            .val()
            .trim();

        let successMessage = popover.find('.milestone-success-message');


        // Empty Validation
        if (milestoneName === '') {

            alert('Please enter milestone name.');

            return;

        }


        // Duplicate Check
        let exists = false;

        $('.milestone-name-select option').each(function () {

            if (
                $(this).val() &&
                $(this).val().toLowerCase() === milestoneName.toLowerCase()
            ) {

                exists = true;

            }

        });


        if (exists) {

            alert('This milestone already exists.');

            return;

        }


        // Create New Option
        let newOption = $('<option>', {

            value: milestoneName,
            text: milestoneName

        });


        // Add Option to All Existing Dropdowns
        $('.milestone-name-select').each(function () {

            $(this).append(newOption.clone());

        });


        // Select New Milestone in Last Dropdown
        $('.milestone-name-select:last').val(milestoneName);


        // Show Success Message
        successMessage.show();

        // Clear Input
        popover.find('.new-milestone-name').val('');

        // Disable Add Button
        button.prop('disabled', true);


        // Close Popover after 1.5 seconds
        setTimeout(function () {

            $('#addMilestoneBtn').popover('hide');

        }, 1500);

    });


    // ========================================
    // Add More Milestone
    // ========================================

    $('#addMilestone').on('click', function () {

        let options = '<option value="">Select Milestone</option>';


        $('.milestone-name-select:first option').each(function () {

            if ($(this).val() !== '') {

                options += $('<div>')
                    .append($(this).clone())
                    .html();

            }

        });


        let html = `

            <div class="milestone-item border rounded p-3 mb-3">

                <div class="d-flex justify-content-between mb-3">

                    <h5 class="milestone-title"></h5>

                    <button type="button"
                        class="btn btn-danger btn-sm removeMilestone">

                        <i class="ti-trash"></i> Remove

                    </button>

                </div>

                <div class="form-group row">

                    <div class="col-sm-4">

                        <label class="col-form-label">
                            Milestone Name
                        </label>

                        <select name="milestones[${milestoneIndex}][milestone_name]"
                            class="form-control milestone-name-select"
                            required>

                            ${options}

                        </select>

                    </div>

                    <div class="col-sm-4">

                        <label class="col-form-label">
                            Percentage (%)
                        </label>

                        <input type="number"
                            name="milestones[${milestoneIndex}][percentage]"
                            class="form-control percentage"
                            min="0.01"
                            max="100"
                            step="0.01"
                            placeholder="Enter percentage"
                            required>

                    </div>

                </div>

            </div>

        `;


        $('#milestoneContainer').append(html);

        milestoneIndex++;

        updateMilestoneTitles();

    });


    // ========================================
    // Remove Milestone
    // ========================================

    $(document).on('click', '.removeMilestone', function () {

        $(this).closest('.milestone-item').remove();

        updateMilestoneTitles();

        calculatePercentage();

    });


    // ========================================
    // Update Milestone Titles
    // ========================================

    function updateMilestoneTitles() {

        $('.milestone-item').each(function (index) {

            $(this).find('.milestone-title').text(
                'Milestone ' + (index + 1)
            );

        });

    }


    // ========================================
    // Percentage Calculation
    // ========================================

    $(document).on('input', '.percentage', function () {

        calculatePercentage();

    });


    function calculatePercentage() {

        let total = 0;

        $('.percentage').each(function () {

            total += parseFloat($(this).val()) || 0;

        });


        total = Math.round(total * 100) / 100;

        $('#totalPercentage').val(total.toFixed(2) + '%');


        if (total === 100) {

            $('#percentageMessage')
                .text('Total percentage is correct.')
                .removeClass('text-danger')
                .addClass('text-success');

        } else {

            $('#percentageMessage')
                .text('Total percentage must be 100%.')
                .removeClass('text-success')
                .addClass('text-danger');

        }

    }


    // Initial Calculation
    calculatePercentage();

});

</script>

@endpush
