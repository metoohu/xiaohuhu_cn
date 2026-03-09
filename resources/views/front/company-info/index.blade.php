@extends('front.layouts.master')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-dark-900 mb-2">巨潮信息公司采集列表</h1>
        <p class="text-slate-600">显示已采集的公司代码、简称、联系电话与采集时间</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        {{-- 工具栏 --}}
        <div class="px-4 sm:px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <p class="text-sm text-slate-500">
                共 <span class="font-medium text-dark-900">{{ $companies->total() }}</span> 条记录
            </p>
            <div class="flex items-center gap-3">
                <a href="{{ url('/company-info?export=1') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium transition-colors">
                    导出 CSV
                </a>
                <a href="{{ url('/crawl-cninfo') }}" class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium transition-colors">
                    手动触发采集
                </a>
            </div>
        </div>

        {{-- 表格 --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3 font-medium text-dark-900 text-left">ID</th>
                        <th class="px-4 py-3 font-medium text-dark-900 text-left">代码</th>
                        <th class="px-4 py-3 font-medium text-dark-900 text-left">公司简称</th>
                        <th class="px-4 py-3 font-medium text-dark-900 text-left">联系电话</th>
                        <th class="px-4 py-3 font-medium text-dark-900 text-left">采集时间</th>
                        <th class="px-4 py-3 font-medium text-dark-900 text-center">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($companies as $company)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3 text-slate-600">{{ $company->id }}</td>
                        <td class="px-4 py-3 font-mono text-dark-900">{{ $company->code }}</td>
                        <td class="px-4 py-3 text-dark-900">{{ $company->abbreviation }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $company->contact_number ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $company->capture_time }}</td>
                        <td class="px-4 py-3 text-center">
                            @if(!empty($company->nature_business))
                            <button type="button" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium transition-colors" data-nature="{{ e($company->nature_business) }}">
                                查询详情
                            </button>
                            @else
                            <span class="text-slate-400 text-sm">无详情</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-slate-500">
                            暂无采集数据，可点击右上角「手动触发采集」按钮进行采集。
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($companies->hasPages())
        <div class="px-4 sm:px-6 py-4 border-t border-slate-100 bg-slate-50/30">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <p class="text-sm text-slate-600">
                    显示第 <span class="font-medium">{{ $companies->firstItem() }}</span> 至 <span class="font-medium">{{ $companies->lastItem() }}</span> 条，共 <span class="font-medium">{{ $companies->total() }}</span> 条
                </p>
                <div>{{ $companies->onEachSide(1)->links() }}</div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- 经营范围详情弹窗 --}}
<div id="nature-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
    <div id="nature-modal-backdrop" class="absolute inset-0 bg-black/60"></div>
    <div class="relative z-10 bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[70vh] flex flex-col border border-slate-100">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <span class="font-semibold text-dark-900">经营范围详情</span>
            <button type="button" id="nature-modal-close" class="text-slate-500 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-6 py-4 overflow-y-auto text-slate-700 leading-relaxed">
            <pre id="nature-modal-content" class="whitespace-pre-wrap font-sans text-sm"></pre>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('nature-modal');
    var modalContent = document.getElementById('nature-modal-content');
    var closeBtn = document.getElementById('nature-modal-close');

    if (!modal || !modalContent || !closeBtn) return;

    function openModal(text) {
        modalContent.textContent = text && text.trim() ? text : '暂无经营范围信息';
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    document.querySelectorAll('[data-nature]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            openModal(this.getAttribute('data-nature') || '');
        });
    });

    closeBtn.addEventListener('click', closeModal);
    document.getElementById('nature-modal-backdrop')?.addEventListener('click', closeModal);
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });
});
</script>
@endpush
@endsection
