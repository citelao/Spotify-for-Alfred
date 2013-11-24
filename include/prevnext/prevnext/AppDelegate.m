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
    [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(popEventHandler:) name:@"ApplicationShouldDisplayImage" object:nil];
    
    NSArray *args = [[NSProcessInfo processInfo] arguments];
    NSString *hasImage = [args objectAtIndex:1];
    
    if([hasImage  isEqual: @"-image"]) {
        NSString *image = [args objectAtIndex:2];
        [[NSNotificationCenter defaultCenter] postNotificationName:@"ApplicationShouldDisplayImage" object:image];
    }
}

- (void)popEventHandler:(NSNotification *)note
{
    NSString *image = [note object];

    [_image setImage: [NSImage imageNamed:image]];
    
    // http://stackoverflow.com/questions/1449035/how-do-i-use-nstimer
    [NSTimer scheduledTimerWithTimeInterval:2.0
                                     target:self
                                   selector:@selector(shouldFadeOutHandler:)
                                   userInfo:nil
                                    repeats:NO];
}

- (void)shouldFadeOutHandler:(NSTimer *)timer
{
    [timer invalidate];
    [NSApp terminate:self];
}

- (void)fadeOutWindow:(NSWindow*)window
{
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
