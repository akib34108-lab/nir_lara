<div class="form-group">
    <label for="name">Name</label>
    <input id="name" name="name" type="text" class="form-control" maxlength="255"
           value="{{ old('name', $amenity->name) }}" required>
    @error('name')<small class="text-danger">{{ $message }}</small>@enderror
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="category">Category</label>
        <select id="category" name="category" class="form-control" required>
            <option value="">Select a category</option>
            @foreach($categories as $value => $label)
                <option value="{{ $value }}" {{ old('category', $amenity->category) === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('category')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
    <div class="form-group col-md-6">
        <label for="status">Status</label>
        <select id="status" name="status" class="form-control" required>
            @foreach($statuses as $value => $label)
                <option value="{{ $value }}" {{ old('status', $amenity->status ?? 'active') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
</div>

<div class="form-group">
    <label for="description">Description</label>
    <textarea id="description" name="description" rows="4" class="form-control">{{ old('description', $amenity->description) }}</textarea>
    @error('description')<small class="text-danger">{{ $message }}</small>@enderror
</div>
