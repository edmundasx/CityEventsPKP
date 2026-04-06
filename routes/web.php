<?php
declare(strict_types=1);

use App\Core\Router;
use App\Middleware\RoleMiddleware;

return function (Router $router): void {
    // Home
    $router->get("/", "HomeController@index");
    $router->get("/home", "HomeController@index");

    $router->get("/api/reverse-geocode", "GeocodeController@reverse");

    // Map
    $router->get("/map", "MapController@index");
    $router->get("/organizers", "OrganizerController@index");
    $router->get("/help", "HelpController@index");
    $router->get("/login", "AuthController@showLogin");
    $router->get("/signup", "AuthController@showSignup");
    $router->post("/login", "AuthController@login");
    $router->post("/register", "AuthController@register");
    $router->post("/logout", "AuthController@logout");

    $router->get("/user/panel", "PanelController@user", [
        new RoleMiddleware(["user"]),
    ]);
    $router->post("/user/panel/favorite-toggle", "UserPanelActionsController@toggleFavorite", [
        new RoleMiddleware(["user"]),
    ]);
    $router->post("/user/panel/notification-read", "UserPanelActionsController@markNotificationRead", [
        new RoleMiddleware(["user"]),
    ]);
    $router->get("/organizer/panel", "PanelController@organizer", [
        new RoleMiddleware(["organizer"]),
    ]);
    $router->get("/organizer/events/create", "OrganizerWorkspaceController@createForm", [
        new RoleMiddleware(["organizer"]),
    ]);
    $router->post("/organizer/events/create", "OrganizerWorkspaceController@create", [
        new RoleMiddleware(["organizer"]),
    ]);
    $router->get("/organizer/events", "OrganizerWorkspaceController@events", [
        new RoleMiddleware(["organizer"]),
    ]);
    $router->get("/organizer/profile", "OrganizerWorkspaceController@profileForm", [
        new RoleMiddleware(["organizer"]),
    ]);
    $router->post("/organizer/profile", "OrganizerWorkspaceController@profileUpdate", [
        new RoleMiddleware(["organizer"]),
    ]);
    $router->get("/admin/panel", "PanelController@admin", [
        new RoleMiddleware(["admin"]),
    ]);
    $router->get("/admin/panel/data", "AdminActionsController@panelData", [
        new RoleMiddleware(["admin"]),
    ]);
    $router->post("/admin/panel/event-status", "AdminActionsController@eventStatus", [
        new RoleMiddleware(["admin"]),
    ]);
    $router->post("/admin/panel/user-role", "AdminActionsController@userRole", [
        new RoleMiddleware(["admin"]),
    ]);

    $router->get("/events/{id:\d+}", "EventController@show");
};
