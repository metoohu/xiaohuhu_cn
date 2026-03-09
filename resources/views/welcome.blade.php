<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - 巨潮资讯公司采集列表</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        {{-- 无 Vite 构建结果时，使用官方 Tailwind CDN，避免报 manifest 错误且保留 Tailwind 排版 --}}
        <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    @endif
</head>
<body class="min-h-screen bg-red-50 text-slate-900 flex flex-col">
    <header class="w-full border-b border-slate-800 px-6 py-4 flex items-center justify-between">
        <h1 class="text-base sm:text-lg font-semibold tracking-wide">
            巨潮资讯公司采集列表
        </h1>
        <div class="flex items-center gap-4">
            <a href="{{ route('front.home') }}" class="text-sm text-slate-400 hover:text-slate-200">首页</a>
            <a href="{{ url('/admin') }}" class="text-sm text-slate-400 hover:text-slate-200">后台管理</a>
            <span class="text-xs sm:text-sm text-slate-400">
                共 {{ $companies->total() }} 条记录
            </span>
        </div>
    </header>

    <main class="flex-1 px-4 sm:px-6 py-6 flex justify-center">
        <div class="w-full max-w-6xl">
            <div class="bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
                    <p class="text-xs sm:text-sm text-slate-600">
                        显示已采集的公司代码、简称、联系电话与采集时间。
                    </p>
                    <div class="flex items-center gap-2">
                        <a
                            href="{{ url('/company-info?export=1') }}"
                            class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium rounded-md bg-green-600 hover:bg-green-500 text-slate-50 border border-green-500/70 transition-colors"
                        >
                            导出
                        </a>
                        <a
                            href="{{ url('/crawl-cninfo') }}"
                            class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium rounded-md bg-blue-600 hover:bg-blue-500 text-slate-50 border border-blue-500/70 transition-colors"
                        >
                            手动触发采集
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-white border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 font-medium text-slate-900 text-xs sm:text-sm">ID</th>
                                <th class="px-4 py-3 font-medium text-slate-900 text-xs sm:text-sm">代码</th>
                                <th class="px-4 py-3 font-medium text-slate-900 text-xs sm:text-sm">公司简称</th>
                                <th class="px-4 py-3 font-medium text-slate-900 text-xs sm:text-sm">联系电话</th>
                                <th class="px-4 py-3 font-medium text-slate-900 text-xs sm:text-sm">采集时间</th>
                                <th class="px-4 py-3 font-medium text-slate-900 text-xs sm:text-sm text-center">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                        @forelse ($companies as $company)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-2.5 text-xs text-slate-600">
                                    {{ $company->id }}
                                </td>
                                <td class="px-4 py-2.5 text-xs font-mono text-slate-900">
                                    {{ $company->code }}
                                </td>
                                <td class="px-4 py-2.5 text-xs text-slate-900">
                                    {{ $company->abbreviation }}
                                </td>
                                <td class="px-4 py-2.5 text-xs text-slate-700">
                                    {{ $company->contact_number ?? '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-xs text-slate-700">
                                    {{ $company->capture_time }}
                                </td>
                                <td class="px-4 py-2.5 text-xs text-center">
                                    @if (! empty($company->nature_business))
                                        <button
                                            type="button"
                                            class="inline-flex items-center px-3 py-1 rounded-md bg-slate-900 hover:bg-slate-700 text-xs text-white border border-slate-700 transition-colors"
                                            data-nature="{{ e($company->nature_business) }}"
                                        >
                                            查询详情
                                        </button>
                                    @else
                                        <span class="text-slate-400">无详情</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-xs text-slate-500">
                                    暂无采集数据，可点击右上角“手动触发采集”按钮进行采集。
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($companies->hasPages())
                    <div class="px-4 sm:px-6 py-3 border-t border-slate-200 bg-white">
                        <div class="flex items-center justify-between">
                            <div class="hidden sm:block text-xs text-slate-900">
                                显示第
                                <span class="font-medium text-slate-900">{{ $companies->firstItem() }}</span>
                                至
                                <span class="font-medium text-slate-900">{{ $companies->lastItem() }}</span>
                                条，总共
                                <span class="font-medium text-slate-900">{{ $companies->total() }}</span>
                                条
                            </div>
                            <div class="w-full sm:w-auto">
                                {{ $companies->onEachSide(1)->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </main>

    {{-- 经营范围详情弹窗 --}}
    <div
        id="nature-modal"
        class="hidden fixed inset-0 z-50 bg-black/60 flex items-center justify-center px-4"
    >
        <div class="bg-pink-50 border border-pink-200 rounded-xl max-w-2xl w-full max-h-[70vh] flex flex-col shadow-[0_24px_60px_rgba(15,23,42,0.55)]">
            <div class="px-4 py-3 border-b border-pink-200 flex items-center justify-between bg-pink-100/70">
                <span class="text-sm font-medium text-pink-950">经营范围详情</span>
                <button
                    type="button"
                    id="nature-modal-close"
                    class="text-xs text-pink-600 hover:text-pink-800 transition-colors"
                >
                    关闭
                </button>
            </div>
            <div class="px-4 py-3 overflow-y-auto text-xs leading-relaxed text-pink-950">
                <pre id="nature-modal-content" class="whitespace-pre-wrap font-sans"></pre>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('nature-modal');
            const modalContent = document.getElementById('nature-modal-content');
            const closeBtn = document.getElementById('nature-modal-close');

            if (!modal || !modalContent || !closeBtn) {
                return;
            }

            function openModal(text) {
                modalContent.textContent = text && text.trim()
                    ? text
                    : '暂无经营范围信息';
                modal.classList.remove('hidden');
            }

            function closeModal() {
                modal.classList.add('hidden');
            }

            document.querySelectorAll('[data-nature]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const text = this.getAttribute('data-nature') || '';
                    openModal(text);
                });
            });

            closeBtn.addEventListener('click', closeModal);

            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });
        });
    </script>
</body>
</html>
