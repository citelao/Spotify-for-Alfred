//
//  MDBorderlessWindow.m
//  Borderless Window
//
//  Created by Mark Douma on 6/19/2010.
//  Copyright 2010 Mark Douma LLC. All rights reserved.
//

#import "MDBorderlessWindow.h"


@implementation MDBorderlessWindow

- (id)initWithContentRect:(NSRect)contentRect styleMask:(NSUInteger)windowStyle backing:(NSBackingStoreType)bufferingType defer:(BOOL)deferCreation {
	
	if (self = [super initWithContentRect:contentRect
								styleMask:NSBorderlessWindowMask
								  backing:NSBackingStoreBuffered defer:deferCreation]) {
//		[self setAlphaValue:1];
        [self setBackgroundColor:[NSColor clearColor]];
		[self setOpaque:NO];
        [self setLevel:NSStatusWindowLevel];
		[self setExcludedFromWindowsMenu:NO];
	}
	return self;
}

- (void)fadeOutAndOrderOut:(BOOL)orderOut {
    if (orderOut) {
        NSTimeInterval delay = [[NSAnimationContext currentContext] duration] + 0.1;
        [self performSelector:@selector(orderOut:) withObject:nil afterDelay:delay];
    }
    [[self animator] setAlphaValue:0.0];
}

@end
