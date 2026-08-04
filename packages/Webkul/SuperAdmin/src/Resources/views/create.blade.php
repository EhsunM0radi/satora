@extends('admin::layouts.master')

@section('page_title', 'ایجاد تننت جدید')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3>ایجاد فروشگاه جدید</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('super_admin.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">نام فروشگاه *</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required maxlength="255">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">شناسه (Slug) *</label>
                            <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug') }}" required maxlength="100" pattern="[a-z0-9-]+">
                            <small class="form-text text-muted">فقط حروف انگلیسی کوچک، اعداد و خط تیره</small>
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="domain" class="form-label">دامنه</label>
                            <input type="text" name="domain" id="domain" class="form-control @error('domain') is-invalid @enderror"
                                   value="{{ old('domain') }}" maxlength="255">
                            @error('domain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="business_type" class="form-label">نوع کسب‌وکار</label>
                            <select name="business_type" id="business_type" class="form-select @error('business_type') is-invalid @enderror">
                                <option value="">-- انتخاب کنید --</option>
                                <option value="fashion" {{ old('business_type') === 'fashion' ? 'selected' : '' }}>مد و پوشاک</option>
                                <option value="electronics" {{ old('business_type') === 'electronics' ? 'selected' : '' }}>الکترونیک</option>
                                <option value="grocery" {{ old('business_type') === 'grocery' ? 'selected' : '' }}>سوپرمارکت</option>
                                <option value="beauty" {{ old('business_type') === 'beauty' ? 'selected' : '' }}>زیبایی</option>
                                <option value="digital" {{ old('business_type') === 'digital' ? 'selected' : '' }}>محصولات دیجیتال</option>
                                <option value="furniture" {{ old('business_type') === 'furniture' ? 'selected' : '' }}>مبلمان</option>
                                <option value="diverse" {{ old('business_type') === 'diverse' ? 'selected' : '' }}>متفرقه</option>
                                <option value="custom" {{ old('business_type') === 'custom' ? 'selected' : '' }}>سفارشی</option>
                            </select>
                            @error('business_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="locale" class="form-label">زبان</label>
                            <select name="locale" id="locale" class="form-select @error('locale') is-invalid @enderror">
                                <option value="fa" {{ old('locale', 'fa') === 'fa' ? 'selected' : '' }}>فارسی</option>
                                <option value="en" {{ old('locale') === 'en' ? 'selected' : '' }}>English</option>
                                <option value="ar" {{ old('locale') === 'ar' ? 'selected' : '' }}>العربية</option>
                                <option value="tr" {{ old('locale') === 'tr' ? 'selected' : '' }}>Türkçe</option>
                            </select>
                            @error('locale') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                       class="form-check-input" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label for="is_active" class="form-check-label">فعال</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('super_admin.dashboard') }}" class="btn btn-secondary">بازگشت</a>
                            <button type="submit" class="btn btn-primary">ایجاد فروشگاه</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
