<?php

namespace Webkul\Shop\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Shop\Http\Requests\ContactRequest;
use Webkul\Shop\Http\Resources\CategoryTreeResource;
use Webkul\Shop\Mail\ContactUs;
use Webkul\Theme\Repositories\ThemeCustomizationRepository;

class HomeController extends Controller
{
    const STATUS = 1;

    public function __construct(
        protected ThemeCustomizationRepository $themeCustomizationRepository,
        protected CategoryRepository $categoryRepository
    ) {}

    /**
     * Loads the home page for the storefront.
     * Redirects to installer if no tenant resolved (SaaS central domain).
     */
    public function index(): View|RedirectResponse
    {
        // If no tenant is resolved, this is the central/landing domain — show installer
        if (! app()->bound('current_tenant') || ! app('current_tenant')) {
            return redirect()->route('installer.index');
        }

        $customizations = $this->themeCustomizationRepository->orderBy('sort_order')->findWhere([
            'status' => self::STATUS,
            'channel_id' => core()->getCurrentChannel()->id,
            'theme_code' => core()->getCurrentChannel()->theme,
        ]);

        $categories = $this->categoryRepository->getVisibleCategoryTree(
            core()->getCurrentChannel()->root_category_id
        );

        $categories = CategoryTreeResource::collection($categories);

        return view('shop::home.index', compact('customizations', 'categories'));
    }

    public function notFound()
    {
        abort(404);
    }

    public function contactUs(): View
    {
        return view('shop::home.contact-us');
    }

    public function sendContactUsMail(ContactRequest $contactRequest): RedirectResponse
    {
        try {
            Mail::queue(new ContactUs($contactRequest->only(['name', 'email', 'contact', 'message'])));
            session()->flash('success', trans('shop::app.home.thanks-for-contact'));
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            report($e);
        }

        return back();
    }
}
