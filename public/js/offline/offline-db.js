/**
 * offline-db.js — IndexedDB wrapper for Denb Field App
 */

const DB_NAME    = 'denb_offline';
const DB_VERSION = 1;

let _db = null;

function openDB() {
    if (_db) return Promise.resolve(_db);

    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, DB_VERSION);

        req.onupgradeneeded = (e) => {
            const db = e.target.result;

            if (!db.objectStoreNames.contains('outbox')) {
                const store = db.createObjectStore('outbox', { keyPath: 'local_uuid' });
                store.createIndex('created_at_mobile', 'created_at_mobile', { unique: false });
                store.createIndex('status', 'status', { unique: false });
            }

            if (!db.objectStoreNames.contains('master_data')) {
                db.createObjectStore('master_data', { keyPath: 'key' });
            }
        };

        req.onsuccess  = (e) => { _db = e.target.result; resolve(_db); };
        req.onerror    = (e) => reject(e.target.error);
    });
}

function uuidv4() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const r = crypto.getRandomValues(new Uint8Array(1))[0] % 16;
        const v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

function txPromise(storeName, mode, callback) {
    return openDB().then((db) => new Promise((resolve, reject) => {
        const tx    = db.transaction(storeName, mode);
        const store = tx.objectStore(storeName);
        const req   = callback(store);
        req.onsuccess = () => resolve(req.result);
        req.onerror   = () => reject(req.error);
    }));
}

export function saveEngagement(record) {
    const draft = {
        ...record,
        local_uuid:        record.local_uuid || uuidv4(),
        created_at_mobile: record.created_at_mobile || new Date().toISOString(),
        _outbox_status:    'pending',
    };
    return txPromise('outbox', 'readwrite', (s) => s.put(draft))
        .then(() => draft.local_uuid);
}

export function getAllDrafts() {
    return openDB().then((db) => new Promise((resolve, reject) => {
        const tx      = db.transaction('outbox', 'readonly');
        const store   = tx.objectStore('outbox');
        const req     = store.getAll();
        req.onsuccess = () => resolve(req.result);
        req.onerror   = () => reject(req.error);
    }));
}

export function getDraft(localUuid) {
    return txPromise('outbox', 'readonly', (s) => s.get(localUuid));
}

export function updateDraftStatus(localUuid, status) {
    return openDB().then((db) => new Promise((resolve, reject) => {
        const tx    = db.transaction('outbox', 'readwrite');
        const store = tx.objectStore('outbox');
        const getReq = store.get(localUuid);
        getReq.onsuccess = () => {
            const record = getReq.result;
            if (!record) return resolve(null);
            record._outbox_status = status;
            const putReq = store.put(record);
            putReq.onsuccess = () => resolve(record);
            putReq.onerror   = () => reject(putReq.error);
        };
        getReq.onerror = () => reject(getReq.error);
    }));
}

export function updateDraftError(localUuid, status, errorMsg) {
    return openDB().then((db) => new Promise((resolve, reject) => {
        const tx    = db.transaction('outbox', 'readwrite');
        const store = tx.objectStore('outbox');
        const getReq = store.get(localUuid);
        getReq.onsuccess = () => {
            const record = getReq.result;
            if (!record) return resolve(null);
            record._outbox_status = status;
            record._sync_error = errorMsg;
            const putReq = store.put(record);
            putReq.onsuccess = () => resolve(record);
            putReq.onerror   = () => reject(putReq.error);
        };
        getReq.onerror = () => reject(getReq.error);
    }));
}

export function deleteDraft(localUuid) {
    return txPromise('outbox', 'readwrite', (s) => s.delete(localUuid));
}

export function clearSyncedDrafts() {
    return openDB().then((db) => new Promise((resolve, reject) => {
        const tx    = db.transaction('outbox', 'readwrite');
        const store = tx.objectStore('outbox');
        const req   = store.getAll();
        req.onsuccess = () => {
            const synced = req.result.filter((r) => r._outbox_status === 'synced');
            if (synced.length === 0) return resolve(0);
            synced.forEach((r) => store.delete(r.local_uuid));
            tx.oncomplete = () => resolve(synced.length);
            tx.onerror    = () => reject(tx.error);
        };
        req.onerror = () => reject(req.error);
    }));
}

export function saveMasterData(payload) {
    return openDB().then((db) => new Promise((resolve, reject) => {
        const tx    = db.transaction('master_data', 'readwrite');
        const store = tx.objectStore('master_data');
        const keys  = ['campaigns', 'sub_cities', 'woredas', 'violation_types'];
        keys.forEach((k) => {
            if (payload[k] !== undefined) {
                store.put({ key: k, data: payload[k], cached_at: payload.cached_at });
            }
        });
        tx.oncomplete = () => resolve(true);
        tx.onerror    = () => reject(tx.error);
    }));
}

export function getMasterData(key) {
    return txPromise('master_data', 'readonly', (s) => s.get(key))
        .then((record) => record ? record.data : null);
}

export { uuidv4 };
