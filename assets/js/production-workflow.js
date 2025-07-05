/**
 * FractalMerch Automated Production Workflow System
 * Orchestrates the entire production pipeline from order to delivery
 */

class ProductionWorkflow {
    constructor(config = {}) {
        this.config = {
            apiBaseUrl: config.apiBaseUrl || '/api/production',
            enableAutomation: config.enableAutomation !== false,
            statusUpdateInterval: config.statusUpdateInterval || 30000, // 30 seconds
            qualityCheckRequired: config.qualityCheckRequired !== false,
            enableNotifications: config.enableNotifications !== false,
            ...config
        };
        
        this.workflows = new Map();
        this.productionStations = new Map();
        this.qualityChecks = new Map();
        this.productionQueue = [];
        this.activeProductions = new Map();
        this.completedProductions = new Map();
        this.updateTimer = null;
        
        this.workflowSteps = {
            'design_review': {
                name: 'Revisión de Diseño',
                estimatedDuration: 2, // hours
                requiredRole: 'designer',
                automated: false,
                prerequisites: [],
                nextSteps: ['design_preparation']
            },
            'design_preparation': {
                name: 'Preparación de Diseño',
                estimatedDuration: 1,
                requiredRole: 'designer',
                automated: false,
                prerequisites: ['design_review'],
                nextSteps: ['production_planning']
            },
            'production_planning': {
                name: 'Planificación de Producción',
                estimatedDuration: 0.5,
                requiredRole: 'production_manager',
                automated: true,
                prerequisites: ['design_preparation'],
                nextSteps: ['material_preparation']
            },
            'material_preparation': {
                name: 'Preparación de Materiales',
                estimatedDuration: 1,
                requiredRole: 'production_staff',
                automated: false,
                prerequisites: ['production_planning'],
                nextSteps: ['printing_sublimation']
            },
            'printing_sublimation': {
                name: 'Impresión/Sublimación',
                estimatedDuration: 3,
                requiredRole: 'print_operator',
                automated: false,
                prerequisites: ['material_preparation'],
                nextSteps: ['quality_check']
            },
            'quality_check': {
                name: 'Control de Calidad',
                estimatedDuration: 1,
                requiredRole: 'quality_inspector',
                automated: false,
                prerequisites: ['printing_sublimation'],
                nextSteps: ['packaging']
            },
            'packaging': {
                name: 'Empaquetado',
                estimatedDuration: 0.5,
                requiredRole: 'packaging_staff',
                automated: false,
                prerequisites: ['quality_check'],
                nextSteps: ['shipping_preparation']
            },
            'shipping_preparation': {
                name: 'Preparación de Envío',
                estimatedDuration: 0.5,
                requiredRole: 'shipping_staff',
                automated: true,
                prerequisites: ['packaging'],
                nextSteps: ['shipped']
            },
            'shipped': {
                name: 'Enviado',
                estimatedDuration: 0,
                requiredRole: 'system',
                automated: true,
                prerequisites: ['shipping_preparation'],
                nextSteps: []
            }
        };
        
        this.productionStations = {
            'design_station_1': { name: 'Estación de Diseño 1', capacity: 3, currentLoad: 0, type: 'design' },
            'design_station_2': { name: 'Estación de Diseño 2', capacity: 3, currentLoad: 0, type: 'design' },
            'print_station_1': { name: 'Impresora Sublimación 1', capacity: 5, currentLoad: 0, type: 'printing' },
            'print_station_2': { name: 'Impresora Sublimación 2', capacity: 5, currentLoad: 0, type: 'printing' },
            'print_station_3': { name: 'Impresora Digital 1', capacity: 3, currentLoad: 0, type: 'printing' },
            'quality_station': { name: 'Control de Calidad', capacity: 10, currentLoad: 0, type: 'quality' },
            'packaging_station': { name: 'Empaquetado', capacity: 15, currentLoad: 0, type: 'packaging' }
        };
        
        this.eventHandlers = {
            workflowStarted: [],
            stepCompleted: [],
            stepFailed: [],
            qualityCheckPassed: [],
            qualityCheckFailed: [],
            productionCompleted: [],
            workflowUpdated: []
        };
        
        this.init();
    }
    
    init() {
        console.log('Initializing Production Workflow System...');
        
        this.loadActiveWorkflows();
        this.setupEventListeners();
        this.setupStatusUpdates();
        this.loadProductionStations();
        this.setupQualityStandards();
        
        console.log('Production Workflow System initialized successfully');
    }
    
    /**
     * Load active workflows from server
     */
    async loadActiveWorkflows() {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/get-workflows.php`);
            const data = await response.json();
            
            if (data.success) {
                data.workflows.forEach(workflow => {
                    this.workflows.set(workflow.id, this.processWorkflow(workflow));
                });
                
                console.log(`Loaded ${this.workflows.size} active workflows`);
            }
        } catch (error) {
            console.error('Error loading workflows:', error);
        }
    }
    
    /**
     * Process raw workflow data
     */
    processWorkflow(workflowData) {
        return {
            id: workflowData.id,
            orderId: workflowData.order_id,
            orderNumber: workflowData.order_number,
            customerId: workflowData.customer_id,
            items: JSON.parse(workflowData.items || '[]'),
            currentStep: workflowData.current_step,
            status: workflowData.status,
            priority: workflowData.priority || 'medium',
            estimatedCompletion: workflowData.estimated_completion ? new Date(workflowData.estimated_completion) : null,
            actualStartTime: workflowData.actual_start_time ? new Date(workflowData.actual_start_time) : null,
            completedSteps: JSON.parse(workflowData.completed_steps || '[]'),
            assignedStations: JSON.parse(workflowData.assigned_stations || '{}'),
            qualityResults: JSON.parse(workflowData.quality_results || '[]'),
            notes: workflowData.notes || '',
            createdAt: new Date(workflowData.created_at),
            updatedAt: new Date(workflowData.updated_at)
        };
    }
    
    /**
     * Start production workflow for order
     */
    async startWorkflow(orderData) {
        try {
            const workflow = {
                orderId: orderData.id,
                orderNumber: orderData.orderNumber,
                customerId: orderData.customerId,
                items: orderData.items,
                currentStep: 'design_review',
                status: 'active',
                priority: this.calculatePriority(orderData),
                estimatedCompletion: this.calculateEstimatedCompletion(orderData),
                actualStartTime: new Date(),
                completedSteps: [],
                assignedStations: {},
                qualityResults: [],
                notes: `Workflow started for order ${orderData.orderNumber}`
            };
            
            const response = await fetch(`${this.config.apiBaseUrl}/create-workflow.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(workflow)
            });
            
            const data = await response.json();
            
            if (data.success) {
                workflow.id = data.workflowId;
                this.workflows.set(workflow.id, workflow);
                this.activeProductions.set(workflow.id, workflow);
                
                // Assign to appropriate stations
                await this.assignToStations(workflow);
                
                // Start first step if automated
                if (this.config.enableAutomation) {
                    await this.processAutomatedSteps(workflow);
                }
                
                this.triggerEvent('workflowStarted', { workflow });
                console.log(`Production workflow started for order ${orderData.orderNumber}`);
                
                return workflow;
            } else {
                throw new Error(data.error || 'Failed to create workflow');
            }
        } catch (error) {
            console.error('Error starting workflow:', error);
            throw error;
        }
    }
    
    /**
     * Calculate workflow priority
     */
    calculatePriority(orderData) {
        let priority = 'medium';
        
        // Rush orders
        if (orderData.customData?.isRush) {
            priority = 'high';
        }
        
        // Large orders
        const totalQuantity = orderData.items.reduce((sum, item) => sum + item.quantity, 0);
        if (totalQuantity > 50) {
            priority = 'high';
        }
        
        // VIP customers
        if (orderData.customData?.isVIP) {
            priority = 'high';
        }
        
        // Express shipping
        if (orderData.shippingMethod === 'express' || orderData.shippingMethod === 'overnight') {
            priority = 'high';
        }
        
        return priority;
    }
    
    /**
     * Calculate estimated completion time
     */
    calculateEstimatedCompletion(orderData) {
        let totalHours = 0;
        
        orderData.items.forEach(item => {
            if (item.isCustom) {
                totalHours += 6; // Custom items take longer
            } else {
                totalHours += 3; // Standard items
            }
        });
        
        // Add base workflow time
        Object.values(this.workflowSteps).forEach(step => {
            totalHours += step.estimatedDuration;
        });
        
        // Add buffer for high-priority orders (rushed work)
        if (this.calculatePriority(orderData) === 'high') {
            totalHours *= 0.8; // 20% faster
        } else {
            totalHours *= 1.2; // 20% buffer
        }
        
        const completionDate = new Date();
        completionDate.setHours(completionDate.getHours() + totalHours);
        
        return completionDate;
    }
    
    /**
     * Assign workflow to production stations
     */
    async assignToStations(workflow) {
        const assignments = {};
        
        // Assign based on workflow steps and station availability
        for (const [stepName, stepConfig] of Object.entries(this.workflowSteps)) {
            const stationType = this.getStationTypeForStep(stepName);
            const availableStation = this.findAvailableStation(stationType, workflow.priority);
            
            if (availableStation) {
                assignments[stepName] = availableStation.id;
                availableStation.currentLoad++;
            }
        }
        
        workflow.assignedStations = assignments;
        
        // Update workflow in database
        await this.updateWorkflow(workflow);
        
        console.log(`Assigned workflow ${workflow.id} to stations:`, assignments);
    }
    
    /**
     * Get station type for workflow step
     */
    getStationTypeForStep(stepName) {
        const stepToStationType = {
            'design_review': 'design',
            'design_preparation': 'design',
            'printing_sublimation': 'printing',
            'quality_check': 'quality',
            'packaging': 'packaging'
        };
        
        return stepToStationType[stepName] || 'general';
    }
    
    /**
     * Find available production station
     */
    findAvailableStation(stationType, priority = 'medium') {
        const stations = Array.from(this.productionStations.values())
            .filter(station => station.type === stationType)
            .filter(station => station.currentLoad < station.capacity);
        
        if (stations.length === 0) return null;
        
        // For high priority, prefer stations with lowest load
        if (priority === 'high') {
            stations.sort((a, b) => a.currentLoad - b.currentLoad);
        }
        
        return stations[0];
    }
    
    /**
     * Complete workflow step
     */
    async completeStep(workflowId, stepName, notes = '', qualityData = {}) {
        const workflow = this.workflows.get(workflowId);
        if (!workflow) {
            throw new Error('Workflow not found');
        }
        
        if (workflow.currentStep !== stepName) {
            throw new Error(`Cannot complete step ${stepName}. Current step is ${workflow.currentStep}`);
        }
        
        try {
            // Record step completion
            workflow.completedSteps.push({
                step: stepName,
                completedAt: new Date(),
                notes: notes,
                qualityData: qualityData
            });
            
            // If this is a quality check, validate results
            if (stepName === 'quality_check') {
                const qualityResult = await this.performQualityCheck(workflow, qualityData);
                if (!qualityResult.passed) {
                    await this.handleQualityFailure(workflow, qualityResult);
                    return false;
                }
            }
            
            // Release current station
            const currentStation = workflow.assignedStations[stepName];
            if (currentStation && this.productionStations.has(currentStation)) {
                this.productionStations.get(currentStation).currentLoad--;
            }
            
            // Move to next step
            const nextSteps = this.workflowSteps[stepName].nextSteps;
            if (nextSteps.length > 0) {
                workflow.currentStep = nextSteps[0];
                workflow.status = 'active';
                
                // Process automated steps
                if (this.config.enableAutomation) {
                    await this.processAutomatedSteps(workflow);
                }
            } else {
                // Workflow completed
                workflow.status = 'completed';
                workflow.currentStep = 'completed';
                this.completeWorkflow(workflow);
            }
            
            workflow.updatedAt = new Date();
            
            // Update in database
            await this.updateWorkflow(workflow);
            
            this.triggerEvent('stepCompleted', { workflow, completedStep: stepName });
            console.log(`Step ${stepName} completed for workflow ${workflowId}`);
            
            return true;
            
        } catch (error) {
            console.error(`Error completing step ${stepName}:`, error);
            await this.handleStepFailure(workflow, stepName, error.message);
            throw error;
        }
    }
    
    /**
     * Process automated workflow steps
     */
    async processAutomatedSteps(workflow) {
        const currentStepConfig = this.workflowSteps[workflow.currentStep];
        
        if (currentStepConfig && currentStepConfig.automated) {
            console.log(`Auto-processing step: ${workflow.currentStep}`);
            
            switch (workflow.currentStep) {
                case 'production_planning':
                    await this.autoProductionPlanning(workflow);
                    break;
                case 'shipping_preparation':
                    await this.autoShippingPreparation(workflow);
                    break;
                case 'shipped':
                    await this.autoShipped(workflow);
                    break;
            }
        }
    }
    
    /**
     * Auto production planning
     */
    async autoProductionPlanning(workflow) {
        // Calculate optimal production schedule
        const schedule = this.calculateProductionSchedule(workflow);
        
        // Reserve materials
        await this.reserveMaterials(workflow);
        
        // Update workflow with planning data
        workflow.completedSteps.push({
            step: 'production_planning',
            completedAt: new Date(),
            notes: 'Automated production planning completed',
            planningData: schedule
        });
        
        // Move to next step
        workflow.currentStep = 'material_preparation';
        await this.updateWorkflow(workflow);
        
        this.triggerEvent('stepCompleted', { workflow, completedStep: 'production_planning' });
    }
    
    /**
     * Auto shipping preparation
     */
    async autoShippingPreparation(workflow) {
        // Generate shipping label
        const shippingLabel = await this.generateShippingLabel(workflow);
        
        // Update order status
        await this.updateOrderStatus(workflow.orderId, 'ready_to_ship');
        
        // Complete step
        workflow.completedSteps.push({
            step: 'shipping_preparation',
            completedAt: new Date(),
            notes: 'Automated shipping preparation completed',
            shippingLabel: shippingLabel
        });
        
        workflow.currentStep = 'shipped';
        await this.updateWorkflow(workflow);
        
        this.triggerEvent('stepCompleted', { workflow, completedStep: 'shipping_preparation' });
    }
    
    /**
     * Auto shipped
     */
    async autoShipped(workflow) {
        // Mark as shipped
        await this.updateOrderStatus(workflow.orderId, 'shipped');
        
        // Send tracking notification
        await this.sendTrackingNotification(workflow);
        
        // Complete workflow
        this.completeWorkflow(workflow);
    }
    
    /**
     * Perform quality check
     */
    async performQualityCheck(workflow, qualityData) {
        const qualityStandards = this.getQualityStandards(workflow.items);
        const result = {
            workflowId: workflow.id,
            checkedAt: new Date(),
            checkedBy: qualityData.inspectorId || 'system',
            items: [],
            overallResult: 'passed',
            notes: qualityData.notes || ''
        };
        
        // Check each item
        for (const item of workflow.items) {
            const itemCheck = {
                itemId: item.id,
                itemName: item.name,
                checks: [],
                result: 'passed'
            };
            
            // Print quality
            if (qualityData.printQuality >= 8) {
                itemCheck.checks.push({ type: 'print_quality', result: 'passed', score: qualityData.printQuality });
            } else {
                itemCheck.checks.push({ type: 'print_quality', result: 'failed', score: qualityData.printQuality });
                itemCheck.result = 'failed';
            }
            
            // Color accuracy
            if (qualityData.colorAccuracy >= 8) {
                itemCheck.checks.push({ type: 'color_accuracy', result: 'passed', score: qualityData.colorAccuracy });
            } else {
                itemCheck.checks.push({ type: 'color_accuracy', result: 'failed', score: qualityData.colorAccuracy });
                itemCheck.result = 'failed';
            }
            
            // Material quality
            if (qualityData.materialQuality >= 8) {
                itemCheck.checks.push({ type: 'material_quality', result: 'passed', score: qualityData.materialQuality });
            } else {
                itemCheck.checks.push({ type: 'material_quality', result: 'failed', score: qualityData.materialQuality });
                itemCheck.result = 'failed';
            }
            
            if (itemCheck.result === 'failed') {
                result.overallResult = 'failed';
            }
            
            result.items.push(itemCheck);
        }
        
        // Store quality result
        workflow.qualityResults.push(result);
        
        if (result.overallResult === 'passed') {
            this.triggerEvent('qualityCheckPassed', { workflow, qualityResult: result });
        } else {
            this.triggerEvent('qualityCheckFailed', { workflow, qualityResult: result });
        }
        
        return result;
    }
    
    /**
     * Handle quality check failure
     */
    async handleQualityFailure(workflow, qualityResult) {
        console.log(`Quality check failed for workflow ${workflow.id}`);
        
        // Determine if rework is possible
        const failedItems = qualityResult.items.filter(item => item.result === 'failed');
        
        if (failedItems.length <= workflow.items.length * 0.3) { // Less than 30% failed
            // Send back to production
            workflow.currentStep = 'printing_sublimation';
            workflow.status = 'rework_required';
            workflow.notes += `\nQuality check failed. Rework required for ${failedItems.length} items.`;
            
            await this.updateWorkflow(workflow);
            
            // Notify production team
            this.sendReworkNotification(workflow, failedItems);
            
        } else {
            // Too many failures - escalate
            workflow.status = 'quality_escalation';
            workflow.notes += `\nQuality check failed. Escalated due to high failure rate (${failedItems.length}/${workflow.items.length} items).`;
            
            await this.updateWorkflow(workflow);
            
            // Notify management
            this.sendQualityEscalationNotification(workflow, qualityResult);
        }
    }
    
    /**
     * Handle step failure
     */
    async handleStepFailure(workflow, stepName, errorMessage) {
        workflow.status = 'error';
        workflow.notes += `\nStep ${stepName} failed: ${errorMessage}`;
        
        await this.updateWorkflow(workflow);
        
        this.triggerEvent('stepFailed', { workflow, failedStep: stepName, error: errorMessage });
        
        // Send notification to supervisor
        this.sendErrorNotification(workflow, stepName, errorMessage);
    }
    
    /**
     * Complete workflow
     */
    async completeWorkflow(workflow) {
        workflow.status = 'completed';
        workflow.currentStep = 'completed';
        workflow.completedAt = new Date();
        
        // Calculate actual duration
        const duration = workflow.completedAt - workflow.actualStartTime;
        workflow.actualDuration = Math.round(duration / (1000 * 60 * 60 * 100)) / 100; // hours
        
        // Move from active to completed
        this.activeProductions.delete(workflow.id);
        this.completedProductions.set(workflow.id, workflow);
        
        await this.updateWorkflow(workflow);
        
        // Update order status
        await this.updateOrderStatus(workflow.orderId, 'completed');
        
        this.triggerEvent('productionCompleted', { workflow });
        console.log(`Production workflow completed for order ${workflow.orderNumber}`);
        
        // Release all assigned stations
        Object.values(workflow.assignedStations).forEach(stationId => {
            if (this.productionStations.has(stationId)) {
                this.productionStations.get(stationId).currentLoad--;
            }
        });
    }
    
    /**
     * Update workflow in database
     */
    async updateWorkflow(workflow) {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/update-workflow.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(workflow)
            });
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Failed to update workflow');
            }
            
            this.triggerEvent('workflowUpdated', { workflow });
            
        } catch (error) {
            console.error('Error updating workflow:', error);
            throw error;
        }
    }
    
    /**
     * Update order status
     */
    async updateOrderStatus(orderId, status) {
        try {
            // This integrates with the order management system
            if (window.orderManagement) {
                await window.orderManagement.updateOrderStatus(orderId, status, 'Production workflow update');
            }
        } catch (error) {
            console.error('Error updating order status:', error);
        }
    }
    
    /**
     * Setup status updates
     */
    setupStatusUpdates() {
        if (this.config.statusUpdateInterval > 0) {
            this.updateTimer = setInterval(() => {
                this.syncWorkflowStatuses();
            }, this.config.statusUpdateInterval);
        }
    }
    
    /**
     * Sync workflow statuses
     */
    async syncWorkflowStatuses() {
        try {
            const activeWorkflowIds = Array.from(this.activeProductions.keys());
            if (activeWorkflowIds.length === 0) return;
            
            const response = await fetch(`${this.config.apiBaseUrl}/sync-statuses.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ workflowIds: activeWorkflowIds })
            });
            
            const data = await response.json();
            
            if (data.success && data.updates.length > 0) {
                data.updates.forEach(update => {
                    this.applyStatusUpdate(update);
                });
            }
        } catch (error) {
            console.error('Error syncing workflow statuses:', error);
        }
    }
    
    /**
     * Apply status update
     */
    applyStatusUpdate(update) {
        const workflow = this.workflows.get(update.workflow_id);
        if (!workflow) return;
        
        if (update.current_step !== workflow.currentStep || update.status !== workflow.status) {
            workflow.currentStep = update.current_step;
            workflow.status = update.status;
            workflow.updatedAt = new Date(update.updated_at);
            
            this.triggerEvent('workflowUpdated', { workflow, update });
        }
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Listen for order creation
        document.addEventListener('orderCreated', (event) => {
            this.handleOrderCreated(event.detail);
        });
        
        // Listen for order status changes
        document.addEventListener('orderStatusChanged', (event) => {
            this.handleOrderStatusChange(event.detail);
        });
    }
    
    /**
     * Handle order created
     */
    async handleOrderCreated(orderData) {
        if (this.config.enableAutomation && orderData.order.items.some(item => item.requiresProduction)) {
            // Automatically start production workflow for orders that need production
            setTimeout(() => {
                this.startWorkflow(orderData.order);
            }, 5000); // 5 second delay to allow order processing
        }
    }
    
    /**
     * Handle order status change
     */
    handleOrderStatusChange(statusData) {
        // Find workflow for this order
        const workflow = Array.from(this.workflows.values())
            .find(w => w.orderId === statusData.orderId);
        
        if (workflow && statusData.newStatus === 'cancelled') {
            // Cancel production workflow
            this.cancelWorkflow(workflow.id, 'Order cancelled');
        }
    }
    
    /**
     * Cancel workflow
     */
    async cancelWorkflow(workflowId, reason = '') {
        const workflow = this.workflows.get(workflowId);
        if (!workflow) return;
        
        workflow.status = 'cancelled';
        workflow.notes += `\nWorkflow cancelled: ${reason}`;
        
        // Release assigned stations
        Object.values(workflow.assignedStations).forEach(stationId => {
            if (this.productionStations.has(stationId)) {
                this.productionStations.get(stationId).currentLoad--;
            }
        });
        
        this.activeProductions.delete(workflowId);
        
        await this.updateWorkflow(workflow);
        
        console.log(`Workflow ${workflowId} cancelled: ${reason}`);
    }
    
    /**
     * Get workflow statistics
     */
    getWorkflowStatistics() {
        const stats = {
            totalWorkflows: this.workflows.size,
            activeWorkflows: this.activeProductions.size,
            completedWorkflows: this.completedProductions.size,
            averageCompletionTime: 0,
            onTimeDelivery: 0,
            qualityPassRate: 0,
            stationUtilization: {}
        };
        
        // Calculate station utilization
        this.productionStations.forEach((station, id) => {
            stats.stationUtilization[id] = {
                name: station.name,
                utilization: (station.currentLoad / station.capacity) * 100,
                currentLoad: station.currentLoad,
                capacity: station.capacity
            };
        });
        
        // Calculate completion metrics
        const completed = Array.from(this.completedProductions.values());
        if (completed.length > 0) {
            const totalDuration = completed.reduce((sum, w) => sum + (w.actualDuration || 0), 0);
            stats.averageCompletionTime = totalDuration / completed.length;
            
            const onTime = completed.filter(w => 
                w.completedAt <= w.estimatedCompletion
            ).length;
            stats.onTimeDelivery = (onTime / completed.length) * 100;
            
            const passedQuality = completed.filter(w => 
                w.qualityResults.length > 0 && 
                w.qualityResults[w.qualityResults.length - 1].overallResult === 'passed'
            ).length;
            stats.qualityPassRate = (passedQuality / completed.length) * 100;
        }
        
        return stats;
    }
    
    /**
     * Trigger event
     */
    triggerEvent(eventName, data) {
        if (this.eventHandlers[eventName]) {
            this.eventHandlers[eventName].forEach(handler => {
                try {
                    handler(data);
                } catch (error) {
                    console.error(`Error in ${eventName} event handler:`, error);
                }
            });
        }
        
        // Also dispatch DOM event
        document.dispatchEvent(new CustomEvent(`production${eventName.charAt(0).toUpperCase() + eventName.slice(1)}`, {
            detail: data
        }));
    }
    
    /**
     * Add event listener
     */
    on(eventName, handler) {
        if (!this.eventHandlers[eventName]) {
            this.eventHandlers[eventName] = [];
        }
        this.eventHandlers[eventName].push(handler);
    }
    
    /**
     * Cleanup resources
     */
    destroy() {
        if (this.updateTimer) {
            clearInterval(this.updateTimer);
        }
        
        this.workflows.clear();
        this.productionStations.clear();
        this.qualityChecks.clear();
        this.productionQueue = [];
        this.activeProductions.clear();
        this.completedProductions.clear();
        
        console.log('Production Workflow System destroyed');
    }
    
    // Placeholder methods for future implementation
    async loadProductionStations() { /* Load station data from server */ }
    async setupQualityStandards() { /* Load quality standards */ }
    async calculateProductionSchedule(workflow) { /* Calculate optimal schedule */ }
    async reserveMaterials(workflow) { /* Reserve materials for production */ }
    async generateShippingLabel(workflow) { /* Generate shipping label */ }
    async sendTrackingNotification(workflow) { /* Send tracking info to customer */ }
    async sendReworkNotification(workflow, failedItems) { /* Notify production team of rework */ }
    async sendQualityEscalationNotification(workflow, qualityResult) { /* Notify management */ }
    async sendErrorNotification(workflow, stepName, error) { /* Send error notification */ }
    getQualityStandards(items) { /* Get quality standards for items */ }
}

// Initialize production workflow
document.addEventListener('DOMContentLoaded', () => {
    const config = {
        apiBaseUrl: '/api/production',
        enableAutomation: true,
        statusUpdateInterval: 30000,
        qualityCheckRequired: true,
        enableNotifications: true
    };
    
    window.productionWorkflow = new ProductionWorkflow(config);
    
    // Setup global event handlers
    window.productionWorkflow.on('workflowStarted', (data) => {
        console.log('Production workflow started:', data.workflow.orderNumber);
    });
    
    window.productionWorkflow.on('stepCompleted', (data) => {
        console.log(`Step ${data.completedStep} completed for workflow ${data.workflow.id}`);
    });
    
    window.productionWorkflow.on('productionCompleted', (data) => {
        console.log('Production completed for order:', data.workflow.orderNumber);
    });
    
    window.productionWorkflow.on('qualityCheckFailed', (data) => {
        console.warn('Quality check failed for workflow:', data.workflow.id);
    });
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ProductionWorkflow;
}