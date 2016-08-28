---
title: "Permissions"
excerpt: "Learn how to manage permissions with for the Media Module."
---

## Licenses, Watermarks, Settings

You can use the standard Zikula Permission System of the Permissions Module to manage access
to these. Please do note that support for single instances is incomplete!
Use the following schemes:

| Object type | Component |  Instance |
| ----------- | --------- | ----------|
| License     | `CmfcmfMediaModule:license:` | `$id::`
| Watermark   | `CmfcmfMediaModule:watermark:` | `$id::`
| Settings / Upgrade | `CmfcmfMediaModule:settings:` | `::`

## Collections, Media

{% include since.html version="1.1.0" %}

The standard Zikula permission system is _not_ used for media and collections. Instead, a more
powerful and flexible custom permission system has been implemented. It allows fine-grained
permission settings. The permission system is implemented on **collection level**. This means
you cannot set permissions for single media objects, but only for whole collections and all
containing media. This is mainly for performance reasons.

The custom permission system is tightly coupled to the tree structure of the collections. If you
define a permission for _Collection A_, you can select whether or not it shall also applied to all
sub-collections of _Collection A_.

When determining whether or not the current user has access to a collection, the following process
is gone through:

1. Get all permissions for the collection itself and all parent collections sorted by position.
2. Go through the permissions from top to bottom.
   - If the permission level of the permission is sufficient, grant access.
   - If the permission level of the permission is insufficient and goOn set to false, deny access.
   - If the permission level of the permission is insufficient but goOn set to true, check the next
   permission.


### Permission levels

The permission system uses different permission levels than Zikula's standard permission
system. Here's an explanation of the permission levels:

| Level | Explanation |
| ----- | ----------- |
| PERM_LEVEL_NONE | The user doesn't have any permission. |
| PERM_LEVEL_OVERVIEW | The user sees all media and all sub-collections he has access to. |
| PERM_LEVEL_DOWNLOAD_COLLECTION | The user can download the whole collection. |
| PERM_LEVEL_MEDIA_DETAILS | The user has access to the detail page of the media objects. |
| PERM_LEVEL_DOWNLOAD_SINGLE_MEDIUM | The user can download a single medium from the details page of the media objects. |
| PERM_LEVEL_EDIT_MEDIA | The user can edit all media objects of the collection. |
| PERM_LEVEL_EDIT_COLLECTION | The user can edit the collection itself and also move it into another collection. |
| PERM_LEVEL_ADD_MEDIA | The user can add new media. |
| PERM_LEVEL_ADD_SUB_COLLECTIONS | The user can add new sub-collections. |
| PERM_LEVEL_DELETE_MEDIA | The user can delete media objects. |
| PERM_LEVEL_DELETE_COLLECTION | The user can delete the collection, including all media objects. |
| PERM_LEVEL_ENHANCE_PERMISSIONS | The user can add permissions with goOn = 1 (learn about it below). He can basically "invite" other people but not "ban" others. |
| PERM_LEVEL_CHANGE_PERMISSIONS | The user can change, remove and add permissions. |

Here's a side-by-side comparison of the available permission levels with the standard Zikula levels:

| Zikula | Media Module |
| ------ | ------------ |
| ACCESS_INVALID | -- |
| ACCESS_NONE | PERM_LEVEL_NONE |
| ACCESS_OVERVIEW | PERM_LEVEL_OVERVIEW, PERM_LEVEL_DOWNLOAD_COLLECTION |
| ACCESS_READ | PERM_LEVEL_MEDIA_DETAILS, PERM_LEVEL_DOWNLOAD_SINGLE_MEDIUM |
| ACCESS_COMMENT | -- |
| ACCESS_MODERATE | -- |
| ACCESS_EDIT | PERM_LEVEL_EDIT_MEDIA, PERM_LEVEL_EDIT_COLLECTION |
| ACCESS_ADD | PERM_LEVEL_ADD_MEDIA, PERM_LEVEL_ADD_SUB_COLLECTIONS |
| ACCESS_DELETE | PERM_LEVEL_DELETE_MEDIA, PERM_LEVEL_DELETE_COLLECTION |
| ACCESS_ADMIN | PERM_LEVEL_ENHANCE_PERMISSIONS, PERM_LEVEL_CHANGE_PERMISSIONS |

### Permission settings

Each permission entry has the following settings and fields:

1. The granted permission level(s)
2. A description so that you can easily remember what you created the permission for.
3. Where the permission applies to. At least one must be enabled.
   - Applies to self: The permission only applies to the collection itself.
   - Applies to sub-collections: The permission only applies to sub-collections.
4. The "goOn" setting: If set to true, the system will continue to search for another permission
   to grant access. If set to false and the permission level of the current permission is not
   sufficient, access is denied.
5. Validity: On the one hand, you can define a date after which the permission is automatically
   disabled. On the other hand, you can define a date until the permission is disabled.
6. The position of the permission.
7. Additional fields defined by the permission type.

### Permission types

There are three different kinds of permissions. When evaluating whether or not the current
user has access to the collection, only permissions applying to the current user are taken
into account.

#### User permissions

The user permission applies to one or more users. You can also select the "guest" user which
matches all non-logged-in people.

#### Group permissions

The group permission applies to one, multiple or all groups.

#### Owner permissions

The owner permission only applies to the creator of the respective collection.
