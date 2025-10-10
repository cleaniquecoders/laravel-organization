<?php

use CleaniqueCoders\LaravelOrganization\Enums\OrganizationRole;

describe('OrganizationRole Enum', function () {
    it('has correct values', function () {
        expect(OrganizationRole::MEMBER->value)->toBe('member')
            ->and(OrganizationRole::ADMINISTRATOR->value)->toBe('administrator');
    });

    it('has all expected cases', function () {
        $cases = OrganizationRole::cases();
        $values = array_map(fn ($case) => $case->value, $cases);

        expect($values)->toBe(['member', 'administrator'])
            ->and(count($cases))->toBe(2);
    });

    it('provides correct labels', function () {
        expect(OrganizationRole::MEMBER->label())->toBe('Member')
            ->and(OrganizationRole::ADMINISTRATOR->label())->toBe('Administrator');
    });

    it('provides correct descriptions', function () {
        expect(OrganizationRole::MEMBER->description())
            ->toBe(__('Regular member with basic access to organization resources.'))
            ->and(OrganizationRole::ADMINISTRATOR->description())
            ->toBe(__('Administrator with full management access to the organization.'));
    });

    it('correctly identifies admin privileges', function () {
        expect(OrganizationRole::ADMINISTRATOR->isAdmin())->toBeTrue()
            ->and(OrganizationRole::MEMBER->isAdmin())->toBeFalse();
    });

    it('correctly identifies member role', function () {
        expect(OrganizationRole::MEMBER->isMember())->toBeTrue()
            ->and(OrganizationRole::ADMINISTRATOR->isMember())->toBeFalse();
    });

    it('can be created from string value', function () {
        expect(OrganizationRole::from('member'))->toBe(OrganizationRole::MEMBER)
            ->and(OrganizationRole::from('administrator'))->toBe(OrganizationRole::ADMINISTRATOR);
    });

    it('can try from string value', function () {
        expect(OrganizationRole::tryFrom('member'))->toBe(OrganizationRole::MEMBER)
            ->and(OrganizationRole::tryFrom('invalid'))->toBeNull();
    });

    it('implements enum contract methods', function () {
        // Test that the enum uses the InteractsWithEnum trait methods
        expect(method_exists(OrganizationRole::MEMBER, 'options'))->toBeTrue();
        expect(method_exists(OrganizationRole::MEMBER, 'labels'))->toBeTrue();
        expect(method_exists(OrganizationRole::MEMBER, 'values'))->toBeTrue();
    });

    it('provides all options', function () {
        $options = OrganizationRole::options();

        expect($options)->toBeArray();

        // The format might be different depending on the trait implementation
        // Check if it has the expected values in some format
        $values = array_values($options);
        $keys = array_keys($options);

        expect($values[0]['label'])->toContain('Member')
            ->and($values[1]['label'])->toContain('Administrator');
    });

    it('provides all labels', function () {
        $labels = OrganizationRole::labels();

        expect($labels)->toBeArray()
            ->and($labels)->toBe(['Member', 'Administrator']);
    });

    it('provides all values', function () {
        $values = OrganizationRole::values();

        expect($values)->toBeArray()
            ->and($values)->toBe(['member', 'administrator']);
    });
});
