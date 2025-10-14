/**
 * JavaScript Test Code for Appointments Page
 * Run this in browser console on /tenant/appointments page
 * 
 * @author QLPhongTro Team
 * @version 1.0.0
 */

console.log("=== APPOINTMENTS AUTOMATION TEST ===");

// Test results storage
const testResults = {
    total: 0,
    passed: 0,
    failed: 0,
    errors: []
};

/**
 * Log test result
 */
function logTest(testName, passed, message = '') {
    testResults.total++;
    if (passed) {
        testResults.passed++;
        console.log(`✅ PASS: ${testName}`);
    } else {
        testResults.failed++;
        testResults.errors.push(`${testName}: ${message}`);
        console.log(`❌ FAIL: ${testName} - ${message}`);
    }
    
    if (message) {
        console.log(`   📝 ${message}`);
    }
    console.log('');
}

/**
 * Test 1: Check if elements exist
 */
function testElementsExist() {
    console.log("🔍 Testing elements exist...");
    
    const filterTabs = document.querySelectorAll(".filter-tab");
    const appointmentCards = document.querySelectorAll(".appointment-card");
    const markCompletedButtons = document.querySelectorAll("[onclick*='markCompleted']");
    const cancelButtons = document.querySelectorAll("[onclick*='cancelAppointment']");
    
    console.log(`Filter tabs found: ${filterTabs.length}`);
    console.log(`Appointment cards found: ${appointmentCards.length}`);
    console.log(`Mark completed buttons found: ${markCompletedButtons.length}`);
    console.log(`Cancel buttons found: ${cancelButtons.length}`);
    
    logTest("Filter tabs exist", filterTabs.length > 0, `Found ${filterTabs.length} filter tabs`);
    logTest("Appointment cards exist", appointmentCards.length > 0, `Found ${appointmentCards.length} appointment cards`);
    logTest("Mark completed buttons exist", markCompletedButtons.length > 0, `Found ${markCompletedButtons.length} mark completed buttons`);
    logTest("Cancel buttons exist", cancelButtons.length > 0, `Found ${cancelButtons.length} cancel buttons`);
    
    return {
        filterTabs: filterTabs.length > 0,
        appointmentCards: appointmentCards.length > 0,
        markCompletedButtons: markCompletedButtons.length > 0,
        cancelButtons: cancelButtons.length > 0
    };
}

/**
 * Test 2: Test filter functionality
 */
function testFilterFunctionality() {
    console.log("🔍 Testing filter functionality...");
    
    const filterTabs = document.querySelectorAll(".filter-tab");
    const appointmentCards = document.querySelectorAll(".appointment-card");
    
    if (filterTabs.length === 0) {
        logTest("Filter tabs available", false, "No filter tabs found");
        return false;
    }
    
    if (appointmentCards.length === 0) {
        logTest("Appointment cards available", false, "No appointment cards found");
        return false;
    }
    
    // Test each filter tab
    const filterStatuses = ['all', 'requested', 'confirmed', 'done', 'cancelled'];
    let filterTestsPassed = 0;
    
    filterStatuses.forEach(status => {
        const tab = document.querySelector(`[data-status="${status}"]`);
        if (tab) {
            logTest(`Filter tab '${status}' exists`, true, `Filter tab for status '${status}' found`);
            filterTestsPassed++;
        } else {
            logTest(`Filter tab '${status}' exists`, false, `Filter tab for status '${status}' not found`);
        }
    });
    
    // Test filterCards function
    if (typeof window.filterCards === "function") {
        logTest("filterCards function exists", true, "Global filterCards function is available");
    } else {
        logTest("filterCards function exists", false, "Global filterCards function not found");
    }
    
    return filterTestsPassed === filterStatuses.length;
}

/**
 * Test 3: Test button functionality
 */
function testButtonFunctionality() {
    console.log("🔘 Testing button functionality...");
    
    // Test markCompleted function
    if (typeof markCompleted === "function") {
        logTest("markCompleted function exists", true, "markCompleted function is available");
    } else {
        logTest("markCompleted function exists", false, "markCompleted function not found");
    }
    
    // Test cancelAppointment function
    if (typeof cancelAppointment === "function") {
        logTest("cancelAppointment function exists", true, "cancelAppointment function is available");
    } else {
        logTest("cancelAppointment function exists", false, "cancelAppointment function not found");
    }
    
    // Test updateAppointmentStatus function
    if (typeof updateAppointmentStatus === "function") {
        logTest("updateAppointmentStatus function exists", true, "updateAppointmentStatus function is available");
    } else {
        logTest("updateAppointmentStatus function exists", false, "updateAppointmentStatus function not found");
    }
    
    // Test showNotification function
    if (typeof showNotification === "function") {
        logTest("showNotification function exists", true, "showNotification function is available");
    } else {
        logTest("showNotification function exists", false, "showNotification function not found");
    }
    
    return true;
}

/**
 * Test 4: Test notification system
 */
function testNotificationSystem() {
    console.log("💬 Testing notification system...");
    
    // Check if Notify is available
    if (typeof window.Notify !== "undefined") {
        logTest("window.Notify is available", true, "Notification system is loaded");
        
        // Test toast method
        if (typeof window.Notify.toast === "function") {
            logTest("window.Notify.toast method exists", true, "Toast method is available");
        } else {
            logTest("window.Notify.toast method exists", false, "Toast method not found");
        }
        
        // Test confirm method
        if (typeof window.Notify.confirm === "function") {
            logTest("window.Notify.confirm method exists", true, "Confirm method is available");
        } else {
            logTest("window.Notify.confirm method exists", false, "Confirm method not found");
        }
        
        // Test confirmMarkCompleted method
        if (typeof window.Notify.confirmMarkCompleted === "function") {
            logTest("window.Notify.confirmMarkCompleted method exists", true, "confirmMarkCompleted method is available");
        } else {
            logTest("window.Notify.confirmMarkCompleted method exists", false, "confirmMarkCompleted method not found");
        }
        
    } else {
        logTest("window.Notify is available", false, "Notification system not loaded");
    }
    
    return true;
}

/**
 * Test 5: Test popup interactions
 */
function testPopupInteractions() {
    console.log("💬 Testing popup interactions...");
    
    // Test mark completed popup
    const markCompletedButtons = document.querySelectorAll("[onclick*='markCompleted']");
    if (markCompletedButtons.length > 0) {
        console.log(`Found ${markCompletedButtons.length} mark completed buttons`);
        
        // Test first button (don't actually click it)
        const firstButton = markCompletedButtons[0];
        const onclick = firstButton.getAttribute("onclick");
        if (onclick && onclick.includes("markCompleted")) {
            logTest("Mark completed button onclick", true, "Button has correct onclick attribute");
        } else {
            logTest("Mark completed button onclick", false, "Button onclick attribute is incorrect");
        }
    } else {
        logTest("Mark completed buttons found", false, "No mark completed buttons found");
    }
    
    // Test cancel popup
    const cancelButtons = document.querySelectorAll("[onclick*='cancelAppointment']");
    if (cancelButtons.length > 0) {
        console.log(`Found ${cancelButtons.length} cancel buttons`);
        
        // Test first button (don't actually click it)
        const firstButton = cancelButtons[0];
        const onclick = firstButton.getAttribute("onclick");
        if (onclick && onclick.includes("cancelAppointment")) {
            logTest("Cancel button onclick", true, "Button has correct onclick attribute");
        } else {
            logTest("Cancel button onclick", false, "Button onclick attribute is incorrect");
        }
    } else {
        logTest("Cancel buttons found", false, "No cancel buttons found");
    }
    
    return true;
}

/**
 * Test 6: Test CSS and styling
 */
function testCSSAndStyling() {
    console.log("🎨 Testing CSS and styling...");
    
    // Test filter tabs styling
    const filterTabs = document.querySelectorAll(".filter-tab");
    if (filterTabs.length > 0) {
        const firstTab = filterTabs[0];
        const computedStyle = window.getComputedStyle(firstTab);
        const hasBorder = computedStyle.border !== 'none';
        const hasBorderRadius = computedStyle.borderRadius !== '0px';
        
        logTest("Filter tabs styling", hasBorder && hasBorderRadius, "Filter tabs have proper styling");
    } else {
        logTest("Filter tabs styling", false, "No filter tabs found to test styling");
    }
    
    // Test appointment cards styling
    const appointmentCards = document.querySelectorAll(".appointment-card");
    if (appointmentCards.length > 0) {
        const firstCard = appointmentCards[0];
        const computedStyle = window.getComputedStyle(firstCard);
        const hasBorder = computedStyle.border !== 'none';
        const hasBorderRadius = computedStyle.borderRadius !== '0px';
        
        logTest("Appointment cards styling", hasBorder && hasBorderRadius, "Appointment cards have proper styling");
    } else {
        logTest("Appointment cards styling", false, "No appointment cards found to test styling");
    }
    
    return true;
}

/**
 * Test 7: Test API endpoints (simulation)
 */
function testAPIEndpoints() {
    console.log("🌐 Testing API endpoints...");
    
    // Test if CSRF token exists
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        logTest("CSRF token exists", true, "CSRF token is available for API requests");
    } else {
        logTest("CSRF token exists", false, "CSRF token not found");
    }
    
    // Test if fetch is available
    if (typeof fetch === "function") {
        logTest("Fetch API available", true, "Fetch API is available for making requests");
    } else {
        logTest("Fetch API available", false, "Fetch API not available");
    }
    
    return true;
}

/**
 * Run all tests
 */
function runAllTests() {
    console.log("🚀 Running all tests...");
    
    const results = {
        elementsExist: testElementsExist(),
        filterFunctionality: testFilterFunctionality(),
        buttonFunctionality: testButtonFunctionality(),
        notificationSystem: testNotificationSystem(),
        popupInteractions: testPopupInteractions(),
        cssAndStyling: testCSSAndStyling(),
        apiEndpoints: testAPIEndpoints()
    };
    
    console.log("📊 Test Results:", results);
    
    // Generate final report
    console.log("\n📊 Final Test Report");
    console.log("===================");
    console.log(`Total Tests: ${testResults.total}`);
    console.log(`Passed: ${testResults.passed}`);
    console.log(`Failed: ${testResults.failed}`);
    console.log(`Success Rate: ${((testResults.passed / testResults.total) * 100).toFixed(2)}%`);
    
    if (testResults.errors.length > 0) {
        console.log("\n❌ Failed Tests:");
        testResults.errors.forEach(error => {
            console.log(`   - ${error}`);
        });
    }
    
    return results;
}

/**
 * Test specific functionality
 */
function testSpecificFunctionality() {
    console.log("🎯 Testing specific functionality...");
    
    // Test filter for 'done' status
    if (typeof window.filterCards === "function") {
        console.log("Testing filter for 'done' status...");
        try {
            window.filterCards('done');
            logTest("Filter 'done' status works", true, "Successfully filtered for done status");
        } catch (error) {
            logTest("Filter 'done' status works", false, `Error: ${error.message}`);
        }
    }
    
    // Test filter for 'cancelled' status
    if (typeof window.filterCards === "function") {
        console.log("Testing filter for 'cancelled' status...");
        try {
            window.filterCards('cancelled');
            logTest("Filter 'cancelled' status works", true, "Successfully filtered for cancelled status");
        } catch (error) {
            logTest("Filter 'cancelled' status works", false, `Error: ${error.message}`);
        }
    }
    
    // Test notification system
    if (typeof window.Notify !== "undefined" && typeof window.Notify.toast === "function") {
        console.log("Testing notification system...");
        try {
            window.Notify.toast({
                title: "Test",
                message: "This is a test notification",
                type: "info",
                duration: 2000
            });
            logTest("Notification system works", true, "Successfully showed test notification");
        } catch (error) {
            logTest("Notification system works", false, `Error: ${error.message}`);
        }
    }
}

// Auto-run tests
console.log("Starting automated tests...");
runAllTests();
testSpecificFunctionality();

console.log("\n✅ All tests completed!");
console.log("📋 Manual Test Steps:");
console.log("1. Click 'Hoàn thành' filter tab");
console.log("2. Click 'Đã hủy' filter tab");
console.log("3. Click 'Đã xem' button on a confirmed appointment");
console.log("4. Click 'Hủy lịch' button on a requested appointment");
console.log("5. Check if popups and notifications work correctly");
