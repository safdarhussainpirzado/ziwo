    <script>
        function callManager(config) {
            return {
                items: config.items || [],
                stats: config.stats || {},
                callTypes: config.callTypes || [],
                callSubTypes: config.callSubTypes || [],
                status: config.status || 'all',
                permissions: config.permissions || {},
                zones: config.zones || [],
                sectors: config.sectors || [],
                beats: config.beats || [],
                search: '',
                filterZone: '',
                filterSector: '',
                filterBeat: '',
                filterType: '',
                filterSubType: '',
                userScope: config.userScope || null,
                filterStatus: config.status || 'all',
                dateFrom: '',
                dateTo: '',
                totalRecords: config.total || 0,
                totalPages: config.last_page || 1,
                isLoading: false,
                fetchTimeout: null,


                
                async fetchData() {
                    if (this.isLoading) return;
                    this.isLoading = true;
                    try {
                        const params = new URLSearchParams({
                            search: this.search,
                            status: this.filterStatus,
                            zone_id: this.filterZone,
                            sector_id: this.filterSector,
                            beat_id: this.filterBeat,
                            call_type_id: this.filterType,
                            call_sub_type_id: this.filterSubType,
                            date_from: this.dateFrom,
                            date_to: this.dateTo,
                            page: this.page,
                            perPage: this.perPage,
                            sort: this.sortBy,
                            direction: this.sortDirection
                        });
                        
                        const res = await fetch(window.location.pathname + '?' + params.toString(), {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            this.items = data.items;
                            this.stats = data.stats;
                            this.page = data.current_page;
                            this.totalPages = data.last_page;
                            this.totalRecords = data.total;
                        }
                    } catch (e) {
                        console.error('Fetch error:', e);
                    } finally {
                        this.isLoading = false;
                    }
                },

                triggerFetch() {
                    if(this.fetchTimeout) clearTimeout(this.fetchTimeout);
                    // Reset to page 1 ONLY IF the change wasn't pagination itself. 
                    // To handle this properly, the watcher for pagination handles itself independently.
                    this.fetchTimeout = setTimeout(() => {
                        this.fetchData();
                    }, 400);
                },

                init() {
                    if (this.userScope) {
                        if (this.userScope.type === 'zone') this.filterZone = this.userScope.id;
                        if (this.userScope.type === 'sector') this.filterSector = this.userScope.id;
                        if (this.userScope.type === 'beat') this.filterBeat = this.userScope.id;
                    }

                    this.$watch('search', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('filterStatus', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('filterZone', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('filterSector', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('filterBeat', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('filterType', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('filterSubType', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('dateFrom', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('dateTo', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('perPage', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('page', () => { this.triggerFetch(); });
                },

                density: 'spacious',
                viewMode: 'table',
                sortBy: 'call_number',
                sortDirection: 'desc',
                page: 1,
                perPage: 25,
                showSidebar: false,
                showDetailModal: false,
                showTransitionModal: false,
                selectedItem: null,
                targetStatus: '',
                transitionRemarks: '',
                selectedTigerId: '',
                isTransiting: false,
                showConfirmModal: false,
                confirmLoading: false,
                confirmConfig: { title: '', message: '', icon: '', isDanger: false },

                get filteredBeats() {
                    if (!this.filterSector) return [];
                    return this.beats.filter(b => b.sector_id == this.filterSector);
                },

                get allSubTypes() {
                    return this.callSubTypes;
                },

                get filteredSubTypesInFilter() {
                    if (!this.filterType) return [];
                    return this.allSubTypes.filter(st => st.call_type_id == this.filterType);
                },

                get filteredSectors() {
                    if (!this.filterZone) return [];
                    return this.sectors.filter(s => s.zone_id == this.filterZone);
                },

                get pagedItems() { return this.items; },

                sortByItem(field) {
                    if (this.sortBy === field) {
                        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortBy = field;
                        this.sortDirection = 'asc';
                    }
                    this.triggerFetch();
                },

                getSortIcon(field) {
                    if (this.sortBy !== field) return 'fa-sort opacity-20';
                    return this.sortDirection === 'asc' ? 'fa-sort-up text-blue-600' : 'fa-sort-down text-blue-600';
                },

                getAllocatedUnit(item) {
                    if (!item) return 'UNALLOCATED';
                    if (item.tiger) return item.tiger.tiger_code;
                    if (item.inprogress_remarks && (item.inprogress_remarks.includes('[Allocated Asset:') || item.inprogress_remarks.includes('[Allocated Static Asset:'))) {
                        const match = item.inprogress_remarks.match(/\[Allocated (?:Static )?Asset: (.*?)\]/);
                        return match ? match[1] : 'UNALLOCATED';
                    }
                    return 'UNALLOCATED';
                },

                formatDate(dateStr) {
                    if (!dateStr) return '---';
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
                },

                formatTime(dateStr) {
                    if (!dateStr) return '---';
                    const d = new Date(dateStr);
                    return d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                },

                getStatusBadge(status) {
                    const badges = {
                        'pending': '<span class="px-3 py-1 bg-rose-50 text-rose-600 border border-rose-100 rounded-lg text-[9px] font-black uppercase tracking-widest">Pending</span>',
                        'in_progress': '<span class="px-3 py-1 bg-amber-50 text-amber-600 border border-amber-100 rounded-lg text-[9px] font-black uppercase tracking-widest">In-Process</span>',
                        'completed': '<span class="px-3 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg text-[9px] font-black uppercase tracking-widest">Resolved</span>',
                        'forwarded': '<span class="px-3 py-1 bg-blue-50 text-blue-600 border border-blue-100 rounded-lg text-[9px] font-black uppercase tracking-widest">Forwarded</span>',
                        'junk': '<span class="px-3 py-1 bg-slate-50 text-slate-400 border border-slate-100 rounded-lg text-[9px] font-black uppercase tracking-widest">Junk</span>',
                        'cancelled': '<span class="px-3 py-1 bg-slate-100 text-slate-500 border border-slate-200 rounded-lg text-[9px] font-black uppercase tracking-widest">Cancelled</span>'
                    };
                    return badges[status] || status;
                },

                openDetailModal(item) {
                    this.selectedItem = item;
                    this.showDetailModal = true;
                },

                openTransitionModal(item, status) {
                    this.selectedItem = item;
                    this.targetStatus = status;
                    this.transitionRemarks = '';
                    this.selectedTigerId = item.tiger_id || '';
                    this.showTransitionModal = true;
                },

                async submitTransition() {
                    if (this.isTransiting) return;
                    this.isTransiting = true;

                    try {
                        const response = await fetch(`/calls/${this.selectedItem.id}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                status: this.targetStatus,
                                remarks: this.transitionRemarks,
                                tiger_id: this.selectedTigerId
                            })
                        });

                        const result = await response.json();

                        if (response.ok) {
                            const index = this.items.findIndex(i => i.id === this.selectedItem.id);
                            if (index !== -1) {
                                this.items[index].status = this.targetStatus;
                                if (this.selectedTigerId) {
                                    this.items[index].tiger_id = this.selectedTigerId;
                                }
                            }
                            
                            this.showTransitionModal = false;
                            if (window.Notification) window.Notification.success(result.message || 'Mission status synchronized.', 'Protocol Success');
                            
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            if (window.Notification) window.Notification.error(result.message || 'Authorization rejected.', 'Protocol Failure');
                        }
                    } catch (e) {
                        if (window.Notification) window.Notification.error('Communications array offline.', 'Connection Error');
                    } finally {
                        this.isTransiting = false;
                    }
                },

                getPriorityBadge(priority, compact = false) {
                    const configs = {
                        1: { label: 'Critical', bg: 'bg-rose-600', text: 'text-white', icon: 'fa-bolt' },
                        2: { label: 'Urgent', bg: 'bg-orange-500', text: 'text-white', icon: 'fa-triangle-exclamation' },
                        3: { label: 'Normal', bg: 'bg-amber-500', text: 'text-white', icon: 'fa-clock' }
                    };
                    const config = configs[priority] || configs[3];
                    if (compact) {
                        return `<span class="px-2 py-0.5 ${config.bg} ${config.text} rounded text-[9px] font-black uppercase flex items-center gap-1 w-fit justify-center mx-auto shadow-sm"><i class="fa-solid ${config.icon} text-[8px]"></i>${config.label}</span>`;
                    }
                    return `<span class="px-3 py-1 ${config.bg} ${config.text} rounded-lg text-[9px] font-black uppercase tracking-widest shadow-sm flex items-center gap-1.5 w-fit"><i class="fa-solid ${config.icon}"></i>${config.label}</span>`;
                },

                clearFilters() {
                    this.filterStatus = 'all';
                    this.filterZone = this.userScope?.type === 'zone' ? this.userScope.id : '';
                    this.filterSector = this.userScope?.type === 'sector' ? this.userScope.id : '';
                    this.filterBeat = this.userScope?.type === 'beat' ? this.userScope.id : '';
                    this.filterType = '';
                    this.filterSubType = '';
                    this.search = '';
                    this.page = 1;
                    this.dateFrom = '';
                    this.dateTo = '';
                    this.triggerFetch();

                },

                confirmDelete(item) {
                    this.confirmConfig = {
                        title: 'Purge Record',
                        message: `Are you sure you want to permanently purge trace <strong>${item.call_number}</strong>? <br><br>This action cannot be undone.`,
                        icon: 'fa-trash-can',
                        isDanger: true,
                        action: async () => {
                            const response = await fetch(`/calls/${item.id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                }
                            });
                            if (response.ok) {
                                this.items = this.items.filter(i => i.id !== item.id);
                                if (window.showSuccess) showSuccess('Trace purged from directory.');
                            } else {
                                if (window.showError) showError('Command execution failed.');
                            }
                        }
                    };
                    this.showConfirmModal = true;
                },

                async executeConfirmAction() {
                    if (typeof this.confirmConfig.action === 'function') {
                        this.confirmLoading = true;
                        await this.confirmConfig.action();
                        this.confirmLoading = false;
                        this.showConfirmModal = false;
                    }
                }
            }
        }
    </script>
    @endpush
@endsection
