//
//  AppDelegate.m
//  prevnext
//
//  Created by Ben Stolovitz on 10/20/13.
//  Copyright (c) 2013 Ben Stolovitz. All rights reserved.
//
// see http://stackoverflow.com/questions/4446878/how-to-implement-hud-style-window-like-address-books-show-in-large-type/4447132#4447132
//

#import "AppDelegate.h"

@implementation AppDelegate

- (void)applicationWillFinishLaunching:(NSNotification *)aNotification
{
    NSArray *args = [[NSProcessInfo processInfo] arguments];
    
    NSString *action = [args objectAtIndex:1];
    
            [_image setImage: [NSImage imageNamed:@"playing"]];
    
    if ([action isEqualToString: @"paused"]) {
        [_image setImage: [NSImage imageNamed:@"paused"]];
    } else if([action isEqualToString: @"playing"]) {
        [_image setImage: [NSImage imageNamed:@"playing"]];
    } else {
        // Temp
        [_image setImage: [NSImage imageNamed:@"playing"]];
    }
}

-  (void)applicationDidFinishLaunching:(NSNotification *)notification
{
    [NSThread sleepForTimeInterval:2.0];
    [NSApp terminate:self];
}

- (void)fadeOutWindow:(NSWindow*)window{
    float alpha = [window alphaValue];
    [window makeKeyAndOrderFront:self];
    while (alpha > 0) {
        alpha -= 0.05;
        [window setAlphaValue:alpha];
        [NSThread sleepForTimeInterval:0.020];
    }
}

- (NSApplicationTerminateReply)applicationShouldTerminate:(NSApplication *)sender
{
    [self fadeOutWindow: _window];
    return YES;
}

@end
