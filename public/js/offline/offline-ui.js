/**
 * offline-ui.js — Connection status indicator + toast notifications
 */

import { getAllDrafts } from './offline-db.js';
import { boot as bootSync } from './offline-sync.js';

function createStatusPill() {
    const pill = document.createElement('div');
    pill.id    = 'denb-connection-status';
    pill.style.cssText = `
        position: fixed;
        top: 12px;
        right: 16px;
        z-index: 99999;
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.03em;
        cursor: default;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        font-family: system-ui, sans-serif;
    `;

    const dot = document.createElement('span');
    dot.id    = 'denb-status-dot';
    dot.style.cssText = 'width:8px;height:8px;border-radius:50%;display:inline-block;transition:background 0.3s;';

    const label = document.createElement('span');
    label.id    = 'denb-status-label';

    pill.appendChild(dot);
    pill.appendChild(label);
    document.body.appendChild(pill);
    return { pill, dot, label };
}

function updateStatusPill(elements, isOnline, pendingCount) {
    const { pill, dot, label } = elements;
    if (isOnline) {
        pill.style.background  = '#052e16';
        pill.style.border      = '1px solid #16a34a';
        pill.style.color       = '#86efac';
        dot.style.background   = '#22c55e';
        label.textContent      = pendingCount > 0 ? `Online — Syncing ${pendingCount}…` : 'Online ✓';
    } else {
        pill.style.background  = '#1c0a0a';
        pill.style.border      = '1px solid #dc2626';
        pill.style.color       = '#fca5a5';
        dot.style.background   = '#ef4444';
        label.textContent      = pendingCount > 0 ? `Offline — ${pendingCount} Pending` : 'Offline';
    }
}

export function showToast(message, type = 'info', duration = 4000) {
    const colors = {
        success: { bg: '#052e16', border: '#16a34a', text: '#86efac', icon: '✓' },
        warning: { bg: '#1c1209', border: '#d97706', text: '#fcd34d', icon: '⚠' },
        info:    { bg: '#0c1a2e', border: '#2563eb', text: '#93c5fd', icon: 'ℹ' },
        error:   { bg: '#1c0a0a', border: '#dc2626', text: '#fca5a5', icon: '✗' },
    };
    const c = colors[type] || colors.info;

    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        bottom: 24px;
        right: 16px;
        z-index: 99999;
        background: ${c.bg};
        border: 1px solid ${c.border};
        color: ${c.text};
        padding: 10px 16px;
        border-radius: 10px;
        font-size: 13px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        display: flex;
        align-items: center;
        gap: 8px;
        animation: slideInUp 0.3s ease;
    `;
    toast.innerHTML = `<span>${c.icon}</span><span>${message}</span>`;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity    = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

export async function updateOutboxBadge() {
    const drafts = await getAllDrafts();
    const pending = drafts.filter((d) => d._outbox_status === 'pending' || d._outbox_status === 'failed').length;
    const label = document.getElementById('denb-status-label');
    if (label) {
        const baseText = navigator.onLine ? 'Online' : 'Offline';
        label.textContent = pending > 0 ? `${baseText} — ${pending} Pending` : `${baseText}${navigator.onLine ? ' ✓' : ''}`;
    }
    return pending;
}

export function boot() {
    const style = document.createElement('style');
    style.textContent = `@keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }`;
    document.head.appendChild(style);

    const elements = createStatusPill();
    updateStatusPill(elements, navigator.onLine, 0);
    updateOutboxBadge();

    window.addEventListener('online', async () => {
        const count = await updateOutboxBadge();
        updateStatusPill(elements, true, count);
        showToast('Connection restored', 'success');
    });

    window.addEventListener('offline', async () => {
        const count = await updateOutboxBadge();
        updateStatusPill(elements, false, count);
        showToast('You are offline', 'warning');
    });

    window.addEventListener('denb:sync-complete', (e) => updateOutboxBadge());

    bootSync();
}

window.DenbUI = { showToast, updateOutboxBadge };
