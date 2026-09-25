<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';

// Vazio em dev (o proxy do Vite trata do /api); em produção define VITE_API_URL
const API = import.meta.env.VITE_API_URL ?? '';

interface Ticket {
    id: number;
    subject: string;
    body: string;
    category: string | null;
    priority: string | null;
    sentiment: string | null;
    summary: string | null;
    analyzed_at: string | null;
    analysis_error: string | null;
    created_at: string;
}

interface PaginatedTickets {
    data: Ticket[];
    meta: { current_page: number; last_page: number; total: number };
}

const CATEGORY_LABELS: Record<string, string> = {
    billing: 'Faturação',
    technical: 'Técnico',
    account: 'Conta',
    feature_request: 'Pedido de funcionalidade',
    other: 'Outro',
};

const PRIORITY_LABELS: Record<string, string> = {
    low: 'Baixa',
    medium: 'Média',
    high: 'Alta',
    urgent: 'Urgente',
};

const PRIORITY_COLOR: Record<string, string> = {
    low: '#5B6272',
    medium: '#E8A33D',
    high: '#E06A2C',
    urgent: '#D64545',
};

const tickets = ref<Ticket[]>([]);
const page = ref(1);
const lastPage = ref(1);
const total = ref(0);
const loadingList = ref(false);
const submitting = ref(false);
const reanalyzingId = ref<number | null>(null);

const filterCategory = ref('');
const filterPriority = ref('');

const form = ref({ subject: '', body: '' });
const formError = ref('');

async function fetchTickets(targetPage = 1) {
    loadingList.value = true;
    const params = new URLSearchParams({ page: String(targetPage) });
    if (filterCategory.value) params.set('category', filterCategory.value);
    if (filterPriority.value) params.set('priority', filterPriority.value);

    try {
        const res = await fetch(`${API}/api/tickets?${params}`, {
            headers: { Accept: 'application/json' },
        });
        const json: PaginatedTickets = await res.json();
        tickets.value = targetPage === 1 ? json.data : [...tickets.value, ...json.data];
        page.value = json.meta.current_page;
        lastPage.value = json.meta.last_page;
        total.value = json.meta.total;
    } finally {
        loadingList.value = false;
    }
}

function applyFilters() {
    fetchTickets(1);
}

async function submitTicket() {
    formError.value = '';

    if (!form.value.subject.trim() || !form.value.body.trim()) {
        formError.value = 'Preenche o assunto e a mensagem.';
        return;
    }

    submitting.value = true;
    try {
        const res = await fetch(`${API}/api/tickets`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(form.value),
        });

        if (!res.ok) {
            const body = await res.json().catch(() => null);
            formError.value = body?.message ?? 'Não foi possível criar o ticket.';
            return;
        }

        const created: Ticket = await res.json();
        tickets.value = [created, ...tickets.value];
        total.value += 1;
        form.value = { subject: '', body: '' };
    } catch {
        formError.value = 'Falha de rede ao enviar o ticket.';
    } finally {
        submitting.value = false;
    }
}

async function reanalyze(ticket: Ticket) {
    reanalyzingId.value = ticket.id;
    try {
        const res = await fetch(`${API}/api/tickets/${ticket.id}/reanalyze`, {
            method: 'POST',
            headers: { Accept: 'application/json' },
        });
        const updated: Ticket = await res.json();
        const idx = tickets.value.findIndex((t) => t.id === updated.id);
        if (idx !== -1) tickets.value[idx] = updated;
    } finally {
        reanalyzingId.value = null;
    }
}

function relativeTime(iso: string): string {
    const diffMs = Date.now() - new Date(iso).getTime();
    const min = Math.floor(diffMs / 60000);
    if (min < 1) return 'agora mesmo';
    if (min < 60) return `há ${min} min`;
    const hours = Math.floor(min / 60);
    if (hours < 24) return `há ${hours} h`;
    return new Date(iso).toLocaleDateString('pt-PT');
}

const canLoadMore = computed(() => page.value < lastPage.value);

onMounted(() => fetchTickets(1));
</script>

<template>
    <div class="min-h-screen bg-[#14171F] px-6 py-10 text-[#EDEAE2] font-[IBM_Plex_Sans,ui-sans-serif,sans-serif]">
        <div class="mx-auto max-w-3xl">
            <header class="mb-8">
                <h1 class="font-[IBM_Plex_Serif,serif] text-3xl tracking-tight">Triagem de tickets</h1>
                <p class="mt-1 text-sm text-[#8B90A0]">
                    {{ total }} ticket{{ total === 1 ? '' : 's' }} registado{{ total === 1 ? '' : 's' }}
                </p>
            </header>

            <!-- Formulário de intake -->
            <section class="mb-10 border border-[#2A2F3D] bg-[#1B1F2A] p-5">
                <h2 class="mb-4 font-[IBM_Plex_Serif,serif] text-lg">Registar novo ticket</h2>
                <form class="space-y-3" @submit.prevent="submitTicket">
                    <div>
                        <label class="mb-1 block text-xs text-[#8B90A0]" for="subject">Assunto</label>
                        <input
                            id="subject"
                            v-model="form.subject"
                            type="text"
                            maxlength="200"
                            class="w-full border border-[#2A2F3D] bg-[#14171F] px-3 py-2 text-sm outline-none focus:border-[#E8A33D]"
                            placeholder="Ex.: Fui cobrado duas vezes"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-[#8B90A0]" for="body">Mensagem</label>
                        <textarea
                            id="body"
                            v-model="form.body"
                            rows="4"
                            maxlength="10000"
                            class="w-full resize-y border border-[#2A2F3D] bg-[#14171F] px-3 py-2 text-sm outline-none focus:border-[#E8A33D]"
                            placeholder="Descreve o problema tal como o cliente o reportou"
                        ></textarea>
                    </div>
                    <p v-if="formError" class="text-sm text-[#D64545]">{{ formError }}</p>
                    <button
                        type="submit"
                        :disabled="submitting"
                        class="bg-[#E8A33D] px-4 py-2 text-sm font-medium text-[#14171F] transition disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{ submitting ? 'A analisar…' : 'Enviar ticket' }}
                    </button>
                </form>
            </section>

            <!-- Filtros -->
            <section class="mb-4 flex flex-wrap gap-3">
                <select
                    v-model="filterCategory"
                    class="border border-[#2A2F3D] bg-[#1B1F2A] px-3 py-1.5 text-sm text-[#EDEAE2]"
                    @change="applyFilters"
                >
                    <option value="">Todas as categorias</option>
                    <option v-for="(label, key) in CATEGORY_LABELS" :key="key" :value="key">{{ label }}</option>
                </select>
                <select
                    v-model="filterPriority"
                    class="border border-[#2A2F3D] bg-[#1B1F2A] px-3 py-1.5 text-sm text-[#EDEAE2]"
                    @change="applyFilters"
                >
                    <option value="">Todas as prioridades</option>
                    <option v-for="(label, key) in PRIORITY_LABELS" :key="key" :value="key">{{ label }}</option>
                </select>
            </section>

            <!-- Manifesto de tickets -->
            <section class="border border-[#2A2F3D]">
                <div
                    v-for="ticket in tickets"
                    :key="ticket.id"
                    class="flex gap-4 border-b border-[#2A2F3D] bg-[#1B1F2A] p-4 last:border-b-0"
                    :style="{ borderLeft: `3px solid ${ticket.priority ? PRIORITY_COLOR[ticket.priority] : '#3A3F4D'}` }"
                >
                    <div class="min-w-0 flex-1">
                        <div class="flex items-baseline justify-between gap-3">
                            <h3 class="truncate font-[IBM_Plex_Serif,serif] text-base">{{ ticket.subject }}</h3>
                            <span class="shrink-0 text-xs text-[#8B90A0]">{{ relativeTime(ticket.created_at) }}</span>
                        </div>

                        <div v-if="ticket.category || ticket.priority" class="mt-1.5 flex flex-wrap gap-x-3 gap-y-1 text-xs text-[#8B90A0]">
                            <span v-if="ticket.category">{{ CATEGORY_LABELS[ticket.category] ?? ticket.category }}</span>
                            <span v-if="ticket.priority" :style="{ color: PRIORITY_COLOR[ticket.priority] }">
                                Prioridade {{ PRIORITY_LABELS[ticket.priority]?.toLowerCase() ?? ticket.priority }}
                            </span>
                            <span v-if="ticket.sentiment">Tom {{ ticket.sentiment === 'negative' ? 'negativo' : ticket.sentiment === 'positive' ? 'positivo' : 'neutro' }}</span>
                        </div>

                        <p v-if="ticket.summary" class="mt-2 text-sm text-[#C7CAD3]">{{ ticket.summary }}</p>
                        <p v-else-if="ticket.analysis_error" class="mt-2 text-sm text-[#D64545]">
                            Não foi possível analisar: {{ ticket.analysis_error }}
                        </p>
                        <p v-else class="mt-2 text-sm text-[#8B90A0]">Ainda sem análise.</p>

                        <button
                            v-if="ticket.analysis_error"
                            class="mt-2 text-xs text-[#E8A33D] underline decoration-dotted underline-offset-2 disabled:opacity-50"
                            :disabled="reanalyzingId === ticket.id"
                            @click="reanalyze(ticket)"
                        >
                            {{ reanalyzingId === ticket.id ? 'A tentar…' : 'Analisar novamente' }}
                        </button>
                    </div>
                </div>

                <p v-if="!loadingList && tickets.length === 0" class="p-6 text-center text-sm text-[#8B90A0]">
                    Ainda não há tickets. Regista o primeiro acima.
                </p>
            </section>

            <button
                v-if="canLoadMore"
                class="mt-4 w-full border border-[#2A2F3D] py-2 text-sm text-[#8B90A0] hover:text-[#EDEAE2]"
                :disabled="loadingList"
                @click="fetchTickets(page + 1)"
            >
                {{ loadingList ? 'A carregar…' : 'Carregar mais' }}
            </button>
        </div>
    </div>
</template>