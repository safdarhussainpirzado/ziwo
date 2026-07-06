# CRM Management Core - UI/UX Architecture

This document outlines the standard architecture used for management interfaces in the NHMP 130 CRM application. All CRUD (Create, Read, Update, Delete) and state-toggle interfaces should adhere to this standardized architecture to maintain a high-fidelity, cohesive user experience.

## Overview

The CRM utilizes a "Bento-style" layout combined with **Alpine.js** for frontend interactivity and state management, providing a Single Page Application (SPA) feel, while keeping the backend strictly rendered by **Laravel Blade**.

Major components include:
- A standardized Data Grid and Data Table architecture.
- Modular, asynchronous Alpine.js controllers handling form submissions, deletions, and state changes.
- A Universal Confirmation Modal (`components.confirm-modal`) for destructive actions and critical state toggles.

## 1. Universal Confirmation Modal

In order to prevent accidental destructive actions, all standard delete and state toggle elements must use the `components.confirm-modal`. 

### Including the Modal
The modal should be included at the bottom of the management page, just before the closing `</div>` of the main container:

```html
@include('components.confirm-modal')
```

### Alpine.js State
The Alpine.js controller must initialize the standard config properties required for the modal to operate:

```javascript
showConfirmModal: false,
confirmLoading: false,
selectedItem: null,
confirmConfig: { 
    title: '', 
    message: '', 
    icon: '', 
    isDanger: false, 
    action: null 
},
```

The execution function that the modal triggers must also be included in the controller:

```javascript
async executeConfirmAction() {
    if (typeof this.confirmConfig.action === 'function') {
        this.confirmLoading = true;
        await this.confirmConfig.action();
        this.confirmLoading = false;
        this.showConfirmModal = false;
    }
}
```

## 2. Action Standard API

All data grids and tables must standardize their action buttons for consistency.

**Required Action Flow:**
1. **View**: Read-only display of the model instance properties.
2. **Edit**: Inline form modal for updates.
3. **Toggle Status**: Soft toggles an active/inactive boolean (e.g., locking/unlocking accounts, pausing ops parameters).
4. **Purge/Delete**: Completely removes the model from the database.

### Action Buttons UI Standard

**Table View Buttons (Compact Iconography):**
```html
<div class="flex items-center justify-center gap-3">
    <!-- View -->
    <button @click="viewItem(item)" title="Inspect Data" class="text-indigo-400 hover:text-indigo-600 transition-colors text-xs font-black tracking-widest uppercase flex items-center gap-1">
        <i class="fas fa-eye"></i>
    </button>
    
    <!-- Edit -->
    <button @click="editItem(item)" title="Modify properties" class="text-blue-500 hover:text-blue-700 transition-colors text-xs font-black tracking-widest uppercase flex items-center gap-1">
        <i class="fas fa-edit"></i>
    </button>
    
    <!-- Status Toggle (Dependent on status/is_active) -->
    <button @click="confirmStatus(item)" title="Toggle State" :class="item.status === 'active' ? 'text-amber-500 hover:text-amber-700' : 'text-emerald-500 hover:text-emerald-700'" class="transition-colors text-xs font-black tracking-widest uppercase flex items-center gap-1">
        <i class="fas fa-power-off"></i>
    </button>
    
    <!-- Delete -->
    <button @click="confirmDelete(item)" title="Purge Record" class="text-rose-400 hover:text-rose-600 transition-colors text-xs font-black tracking-widest uppercase flex items-center gap-1">
        <i class="fas fa-trash-alt"></i>
    </button>
</div>
```

**Grid View Buttons (Card Formats):**
```html
<div class="flex gap-2">
    <button @click="viewItem(item)" title="Inspect Data" class="w-10 h-10 rounded-xl bg-blue-900 text-white hover:bg-blue-600 transition-all shadow-xl active:scale-95">
        <i class="fa-solid fa-shield-halved"></i>
    </button>
    <button @click="editItem(item)" title="Modify parameters" class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-500 transition-all shadow-sm active:scale-95">
        <i class="fa-solid fa-sliders"></i>
    </button>
    <button @click="confirmStatus(item)" title="Toggle State" :class="item.status === 'active' ? 'text-amber-500' : 'text-emerald-500'" class="w-10 h-10 rounded-xl bg-white border border-slate-200 transition-all shadow-sm active:scale-95">
        <i class="fa-solid fa-power-off"></i>
    </button>
    <button @click="confirmDelete(item)" title="Purge" class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-rose-400 hover:text-rose-600 hover:border-rose-500 transition-all shadow-sm active:scale-95">
        <i class="fa-solid fa-trash-alt"></i>
    </button>
</div>
```

## 3. Asynchronous Execution Methods

All UI handlers map directly to standard controller functions using AJAX. Pages no longer reload on successful operations.

### Status Toggling (`confirmStatus`)

Configures and triggers the Universal Modal for toggling an entity's status:

```javascript
confirmStatus(item) {
    this.selectedItem = item;
    const willBeActive = item.status !== 'active'; // or !item.is_active
    this.confirmConfig = {
        title: willBeActive ? 'Activate Entity' : 'Deactivate Entity',
        message: willBeActive 
            ? `Are you sure you want to activate <strong>${item.name}</strong>?`
            : `Are you sure you want to deactivate <strong>${item.name}</strong>?`,
        icon: 'fa-power-off',
        isDanger: !willBeActive, // Red for deactivation, Yellow/Orange for activation
        action: async () => {
            try {
                const response = await fetch(`/admin/endpoint/${item.id}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const result = await response.json();
                if (result.success) {
                    item.status = result.status; // update local binding
                    showSuccess(`Status updated to ${result.status}`);
                } else {
                    showError("Status synchronization failed");
                }
            } catch (error) {
                showError("Status synchronization failed");
            }
        }
    };
    this.showConfirmModal = true;
}
```

### Deletion (`confirmDelete`)

Configures and triggers the Universal Modal for purging records:

```javascript
confirmDelete(item) {
    this.selectedItem = item;
    this.confirmConfig = {
        title: 'Purge Entity',
        message: `WARNING: Purging <strong>${item.name}</strong> is destructive.<br><br>Proceed with purge?`,
        icon: 'fa-trash-alt',
        isDanger: true, // Always Red
        action: async () => {
            try {
                const response = await fetch(`/admin/endpoint/${item.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                if (response.ok) {
                    // Update state array directly
                    this.items = this.items.filter(i => i.id !== item.id);
                    showSuccess('Entity purged.');
                } else {
                    showError("Purge sequence failed.");
                }
            } catch (error) {
                showError("Purge sequence failed.");
            }
        }
    };
    this.showConfirmModal = true;
}
```

## 4. Notifications

For user feedback, rely entirely on the globally attached Toast bindings:
- `showSuccess('Message block')`: Success green toast.
- `showError('Message block')`: Alert red toast.

Do not re-implement local toast systems. All errors arising during execution block `action()` routines should trigger visually using `showError()`.

## 5. Universal UI Components & Layout Filtering Defaults

Every management module must implement standard UI filters and sorting wrappers.

### Sticky Sidebar Filters
The filtering pane must include universal layout configuration selectors:

1. **Status State Toggle**:
   ```html
   <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
       <i class="fas fa-shield-virus text-emerald-500"></i> Status State
   </label>
   <div class="grid grid-cols-1 gap-2">
       <button @click="filterStatus = ''; searchCategories()" :class="filterStatus === '' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-2 border-slate-100'" ...>Global Data</button>
       <button @click="filterStatus = 'active'; searchCategories()" :class="filterStatus === 'active' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-2 border-slate-100'" ...>Authorized Only</button>
       <button @click="filterStatus = 'inactive'; searchCategories()" :class="filterStatus === 'inactive' ? 'bg-gradient-to-r from-rose-600 to-rose-400 text-white shadow-lg shadow-rose-200' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-2 border-slate-100'" ...>Locked Vaults</button>
   </div>
   ```

2. **Page Density Switcher**:
   ```html
   <div class="space-y-3">
       <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
           <i class="fas fa-compress-alt text-indigo-500"></i> Page Density
       </label>
       <div class="grid grid-cols-2 gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200/50">
           <button @click="density = 'condensed'" :class="density === 'condensed' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="py-2 text-[9px] font-black uppercase tracking-widest rounded-lg transition-all text-slate-500 hover:text-slate-700">Condensed</button>
           <button @click="density = 'spacious'" :class="density === 'spacious' ? 'bg-white text-indigo-600 shadow-sm font-black' : 'text-slate-500 hover:text-slate-700'" class="py-2 text-[9px] font-black uppercase tracking-widest rounded-lg transition-all bg-white text-indigo-600 shadow-sm">Spacious</button>
       </div>
   </div>
   ```

3. **Records Per Page Controls**:
   ```html
   <div class="space-y-3">
       <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
           <i class="fas fa-list-ol text-indigo-500"></i> Records Per Page
       </label>
       <div class="grid grid-cols-4 gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200/50">
           <template x-for="size in [10, 25, 50, 100]" :key="size">
               <button @click="perPage = size; ..." :class="perPage == size ? 'bg-white text-indigo-600 shadow-sm font-black' : 'text-slate-500 hover:text-slate-700'" class="py-2 text-[9px] uppercase tracking-widest rounded-lg transition-all" x-text="size"></button>
           </template>
       </div>
   </div>
   ```

4. **Action Buttons**: At the bottom of the filters pane, standard Reset/Hide controls MUST be included:
   ```html
   <button @click="clearFilters()" class="w-full py-5 text-rose-500 hover:bg-rose-50 rounded-3xl text-[10px] font-black uppercase tracking-[0.3em] transition-all duration-300 flex items-center justify-center gap-3 active:scale-95 border-2 border-rose-100">
       <i class="fas fa-broom"></i> Reset Filters
   </button>
   <button @click="showSidebar = false" class="w-full py-4 mt-2 bg-indigo-600 text-white rounded-3xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-700 transition-all flex items-center justify-between px-6 shadow-md shadow-indigo-600/20">
       <span>Hide Filters</span><i class="fas fa-eye-slash"></i>
   </button>
   ```

### Header Row Density Dropdown
Positioned inline directly before the grid/table layout toggles:
```html
<div class="flex items-center gap-2 bg-white border border-indigo-100 rounded-xl px-3 py-1.5 shadow-sm">
    <span class="text-[9px] font-black text-slate-400 border-r border-slate-100 pr-2 uppercase font-mono">Row Density</span>
    <select x-model="perPage" @change="/* optional fetch */" class="bg-transparent text-indigo-600 text-[10px] font-black uppercase cursor-pointer outline-none focus:ring-0 border-none p-0 pr-4">
        <option value="10">10 Per Page</option>
        <option value="25">25 Per Page</option>
        <option value="50">50 Per Page</option>
        <option value="100">100 Per Page</option>
    </select>
</div>
```

### Table Column Sorting Standard
All valid table headers should integrate structural icon boxes and dynamic sorting indicators:
```html
<th class="px-4 py-4 border-b border-slate-50">
    <div class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
        <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-500 shadow-sm border border-indigo-100">
            <i class="fas fa-tag text-[10px]"></i>
        </div>
        <button @click="sortBy('column_name')" class="flex items-center gap-1.5 hover:text-indigo-700 transition-colors group">
            Column Name
            <i class="fas text-[10px] transition-all duration-300 opacity-0 group-hover:opacity-100 fa-sort-up text-indigo-600 scale-125" :class="getSortIcon('column_name')"></i>
        </button>
    </div>
</th>
```

### Bento-Style Modals
Data-entry (Add/Edit) forms should avoid plain unstyled containers and instead use a Premium Bento styling approach:
- Use inner gradients (e.g. `bg-gradient-to-br from-blue-600 to-blue-800 p-8 text-white`).
- Sub-fields should be grouped in separate padded, rounded card sections with shadow bounds to keep data readable.
- The entire form container should deploy standard light bases like `bg-white` or `bg-slate-50`.

### Action Button Standard — 3-Column Icon Grid
**All** action button sets (table rows and card/grid views) MUST use the same format and color codes. Do NOT mix icon+label buttons with icon-only buttons.

**Definitive Standard: Icon-only `w-9 h-9` square buttons in a `grid grid-cols-3` container.**
This produces a clean 2-row × 3-column layout for up to 6 actions without overflow in any viewport.

**Card (grid) view footer structure:** The status badge (e.g. "Provisioned" with key icon) goes on its own full-width row above the action grid. Use `space-y-3` to separate badge from buttons:

```html
<div class="pt-5 border-t border-slate-100 space-y-3">
    <!-- Badge row — full width single line -->
    <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-key text-xs"></i>
        </div>
        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Provisioned</span>
    </div>
    <!-- Action grid: 3-col × 2-row -->
    <div class="grid grid-cols-3 gap-2">
        <button class="w-9 h-9 ... mx-auto">...</button>
    </div>
</div>

<!-- Table row action grid -->
<div class="inline-grid grid-cols-3 gap-1.5">
    <button class="w-9 h-9 ...">...</button>
</div>
```

| Action         | Button Color         | Icon |
|---------------|----------------------|------|
| View Profile  | `bg-blue-500`        | `fa-eye` |
| Edit / Modify | `bg-indigo-500`      | `fa-sliders` |
| Rotate Key    | `bg-orange-500`      | `fa-key` |
| View Access   | `bg-cyan-600`        | `fa-shield-halved` |
| Toggle State  | `bg-amber-500` / `bg-emerald-500` | `fa-power-off` |
| Delete / Purge | `bg-rose-600`       | `fa-trash-alt` |

Rules:
- Button size: **`w-9 h-9`** in both table and card views (uniform — do NOT use `aspect-square` in cards)
- Border: always 1 shade darker than fill (e.g. `border-blue-600` on `bg-blue-500`)
- Active feedback: `active:scale-95` on all buttons
- **Prohibited**: ghost-only hover buttons, icon+label pills mixed with icon-only, `aspect-square` card buttons

### Filter Sidebar Architecture (Sticky Viewport Scroll)
Index page filter sidebars (right column) must remain visible as the user scrolls down the record list.
- Apply `lg:sticky lg:top-4 lg:self-start lg:max-h-[calc(100vh-100px)] lg:overflow-y-auto` to the filter column container element so it follows the viewport on desktop.
- On mobile the sidebar flows naturally in-document order.


### FontAwesome Stability
Avoid using `fa-shield-check` due to FontAwesome 6 Free missing vectors. 
- Swap strictly to `fa-solid fa-user-shield` or `fa-solid fa-shield-halved` where access logic visuals are needed.

### Alpine JS Data Initialization
When hydrating Alpine components with paginated Laravel data, you MUST pass arrays, not pagination objects, to prevent `.filter` and `.slice` runtime errors in the client.
- **Correct**: `items: {!! json_encode($models->items()) !!},`
- **Prohibited**: `items: {{ $models->toJson() }},`

### Default UI States
To ensure a consistent user experience and layout stability during initial page load, all index views must explicitly initialize their Alpine component states to prioritize performance and data density:
- **`viewMode`**: Must be explicitly set to `'table'` on initial load in all index components (e.g. `viewMode: 'table'`).
- **`showSidebar`**: Must be explicitly set to `false` on initial load to maintain maximum viewport availability for primary record data.

### SPA / Bento Bridge Form Interception
To prevent the lightweight SPA router (`bento-bridge.js`) from intercepting Alpine-driven AJAX forms and triggering background DOM reloads (which destroys modal states and active components like validation feedback):
- **Requirement**: ALL `<form>` tags managed directly by Alpine `@submit.prevent` functions executing asynchronous `fetch()` requests MUST include the `data-no-pjax` attribute.
- **Correct**: `<form @submit.prevent="submitForm" data-no-pjax>`
- **Prohibited**: `<form @submit.prevent="submitForm">`
