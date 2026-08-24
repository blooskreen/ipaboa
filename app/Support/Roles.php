<?php

namespace App\Support;

final class Roles
{
    public const STANDARD    = 'Standard User';
    public const OFFICIAL    = 'Official';
    public const CAMPER      = 'Camper';
    public const LEADERSHIP  = 'Camp Leadership';
    public const CAMP_ADMIN  = 'Camp Administrator';
    public const STORE_ADMIN = 'Store Admin';
    public const ADMIN       = 'Administrator';
    public const SUPER       = 'Super Administrator';

    /**
     * Every role, in display order.
     * NOTE: order is presentation only. Roles are standalone, NOT an
     * inherited ladder. A role gets only what it is explicitly granted.
     */
    public const ALL = [
        self::STANDARD,
        self::OFFICIAL,
        self::CAMPER,
        self::LEADERSHIP,
        self::CAMP_ADMIN,
        self::STORE_ADMIN,
        self::ADMIN,
        self::SUPER,
    ];

    /** Hardcoded door-gate: who may reach /admin at all. */
    public const PANEL = [
        self::SUPER,
        self::ADMIN,
        self::CAMP_ADMIN,
        self::LEADERSHIP,
        self::STORE_ADMIN,
    ];

    /** Always full access, regardless of the section_access table. */
    public const ALWAYS_FULL = [
        self::SUPER,
        self::ADMIN,
    ];

    /** May edit the general roles field on a user. */
    public const CAN_EDIT_ROLES = [
        self::SUPER,
        self::ADMIN,
    ];

    /**
     * Roles that take part in the training program, and therefore see the
     * hours gauge, courses, assessments and certificates on the dashboard.
     * Standard User is deliberately absent: they have registered but are not
     * yet an official. Store Admin is store staff, not an official.
     */
    public const TRAINING = [
        self::OFFICIAL,
        self::CAMPER,
        self::LEADERSHIP,
        self::CAMP_ADMIN,
        self::ADMIN,
        self::SUPER,
    ];

    /** May promote / graduate / renew members. */
    public const CAN_PROMOTE = [
        self::SUPER,
        self::ADMIN,
        self::CAMP_ADMIN,
        self::LEADERSHIP,
    ];
}
